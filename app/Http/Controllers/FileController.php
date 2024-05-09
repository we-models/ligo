<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Models\Field;
use App\Models\File as FileModel;
use App\Models\ImageFile;
use App\Repositories\FileRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\PdfToImage;
use Throwable;

class FileController extends Controller
{

    private FileRepository $fileRepository;

    /**
     * @var string
     */
    public string $object = FileModel::class;

    /**
     * @param FileRepository $fileRepo
     */
    public function __construct(FileRepository $fileRepo)
    {
        $this->fileRepository = $fileRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $obj = new $this->object();
        return view('pages.general.file', ['sorts' => json_encode($obj->sortable)]);
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request) : Response|JsonResponse {

        $rq = getRequestParams($request);
        $files = $this->fileRepository->search($rq->search)
            ->where('user', auth()->user()->getAuthIdentifier());
        if(!isset($request['sort']) || !isset($request['direction'])){
            $files = $files->orderBy('id', 'desc');
        }
        $files = $files->sortable();
        return $this->fileRepository->getResponse($files, $rq);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ResponseFactory|Application|Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        if(isset($input['field']) && $input['field'] != 'undefined' ){
            $mimes = explode(',', Field::query()->where('id',$request['field'])->first()->accept);
            $mimes = array_map(fn ($mime) => trim($mime), $mimes);
        }else{
            $mimes = MIMES;
        }
        set_time_limit(180);


        $mimes = implode(',', $mimes);

        $request->validate([ 'the_file' => 'required|mimetypes:' . $mimes ]);

        $file = $request->file('the_file');

        $y = date('Y');
        $m = date('m');
        $u = auth()->user()->getAuthIdentifier();

        $ext = $file->getClientOriginalExtension();

        $directory = 'files/' . $y. '/'. $m. '/' . $u . '/';
        $name = $this->getFileName($directory, $ext, $file->getClientOriginalName() );
        $name = str_replace(' ', '_', $name);



        $next = FileModel::query()->where('permalink', 'LIKE', $directory . $name . "%" )->count();
        if($next > 0){
            $name = "{$name}_$next";
        }


        $name_with_directory = $directory . $name . '.'. $ext;

        try{
            DB::beginTransaction();
            $file_db = FileModel::query()->create([
                'name' => $name . '.'. $ext,
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'mimetype' => $file->getClientMimeType(),
                'user' => $u,
                'url' => $this->getFileRoute($y, $m, $u, $name . '.'. $ext),
                'permalink' => $name_with_directory
            ]);
            if (!file_exists($directory)) mkdir($directory, 0777, true);
            $file->move($directory, $name . '.'. $ext);

            $file_db->save();

            $img_directory = "images/" . $y. "/". $m. "/" . $u . "/" ;
            if (!file_exists($img_directory)) mkdir($img_directory, 0777, true);

            if(str_starts_with($file->getClientMimeType(), 'video')){

                try{
                    FFMpeg::openUrl($this->getFileRoute($y, $m, $u, $name . '.'. $ext))
                        ->getFrameFromSeconds(1)
                        ->export()
                        ->toDisk('default')
                        ->save($img_directory . $name . '.jpg');


                    $img = $this->saveRelatedImage($img_directory, $name, $u, $y, $m);

                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (Throwable $e){

                }
            }
            if($file->getClientMimeType() == 'application/pdf'){
                try{
                    $name_with_directory = '/'.$name_with_directory;
                    if(stripos(php_uname('s'), 'windows') !== false){
                        $name_with_directory = str_replace('/', '\\', $name_with_directory);
                    }
                    $name_with_directory = public_path() . $name_with_directory;
                    if(!file_exists($name_with_directory)){
                        throw new Exception('file no exists');
                    }

                    $pdf = new PdfToImage\Pdf($name_with_directory);
                    $pdf->setOutputFormat('jpg');

                    $pdf->saveImage(public_path() .'/'. $img_directory .  $name . '.jpg');

                    $img = $this->saveRelatedImage($img_directory, $name, $u, $y, $m);
                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (Throwable $e){
                    $theError = $e;
                }
            }

            DB::commit();

            $file_db = FileModel::query()->where('id',$file_db->id)->with(['user','images'])->first();

            return response()->json($file_db, 200);
        }catch (Throwable $e){
            DB::rollBack();

            DB::beginTransaction();
            ErrorLog::query()->create([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::commit();

            File::delete($name_with_directory);
            if(Storage::exists($name_with_directory))  Storage::delete($name_with_directory);
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function saveRelatedImage($img_directory, $name, $u, $y, $m): Model|Builder
    {
        if (!file_exists($img_directory)) mkdir($img_directory, 0777, true);

        $img_f = Storage::disk('default')->get($img_directory . $name . '.jpg');

        $img_file = ImageManager::imagick()->read($img_f);


        $img = ImageFile::query()->create([
            'name' => $name . '.jpg',
            'height' => $img_file->height(),
            'width' => $img_file->width(),
            'size' => strlen($img_f),
            'extension' => 'jpg',
            'mimetype' => 'image/jpg',
            'user' => $u,
            'url' => $this->getImageRoute($y, $m, $u, $name . '.jpg'),
            'permalink' => $img_directory . $name . '.jpg'
        ]);
        $this->imageSaving($img_f, $img->url);

        $img->save();

        return $img;
    }


    public function getImageRoute($y, $m, $u, $i): string {
        return route('image.getImage',[
            'y' => $y,
            'm' =>$m,
            'u' => $u,
            'i' => $i
        ]);
    }

    function imageSaving($image, $name): void
    {
        $name = str_replace(URL::to('/').'/', '', $name);
        $image = ImageManager::imagick()->read($image);
        $image->save(public_path($name));
    }

    function getFileName($directory, $extension , $nm): string {
        $name = explode(".", $nm);
        if(count($name) == 1 || end($name) !== $extension) {
            $name = $nm . '.' . $extension;
            $name = explode('.', $name);
        }
        $name = implode('.', $name);
        $validateFile = explode('.',$name);
        array_pop($validateFile);
        $validateFile = implode('.', $validateFile);
        $directory_find = str_replace('\\', '\\\\', $directory);
        $fileExists =  FileModel::query()->where('permalink', 'like',$directory_find . $validateFile . '%')->count();
        $fileExists = ($fileExists === 0) ? '' : '_' . ($fileExists + 1);
        return $validateFile . $fileExists;
    }

    public function getFileRoute($y, $m, $u, $f): string {
        return route('file.getFile',[
            'y' => $y,
            'm' =>$m,
            'u' => $u,
            'f' => $f
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    public function getFile(Request $request, $y, $m, $u, $i ) {
        $default_path = 'default\\default.jpg';
        $file= Storage::get($default_path);

        $the_file = $request->getRequestUri();
        $the_file = explode('?', $the_file);
        $the_file = $the_file[0];
        $the_file = FileModel::query()
            ->where('url', 'like', '%' . $the_file)
            ->first();

        if($the_file == null)
            return response()->make($file, 200, ["Content-Type" => Storage::mimeType($default_path)]);

        $file = Storage::get($the_file->permalink);

        return response()->make($file, 200, ["Content-Type" => $the_file->mimetype]);
    }

}
