<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ErrorLog;
use App\Models\Field;
use App\Models\File as FileModel;
use App\Models\ImageFile;
use App\Repositories\FileRepository;
use FFMpeg\Coordinate\TimeCode;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use ProtoneMedia\LaravelFFMpeg\Filters\TileFactory;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\PdfToImage;

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

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();

        $rq = getRequestParams($request);
        $files = $this->fileRepository->search($rq->search)
            ->where('user', auth()->user()->getAuthIdentifier())
            ->where('business', $business->id);
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
    public function store(Request $request): Application|ResponseFactory|Response
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
        $b = session('business');
        $u = auth()->user()->getAuthIdentifier();
        $v = in_array($input['visibility'], IMAGE_LOCATIONS) ? $input['visibility'] : 'public' ;

        $ext = $file->getClientOriginalExtension();

        $directory = 'files/' . $y. '/'. $m. '/' . $b . '/' . $u . '/' . $v . '/';

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
        $name = $this->getFileName($directory, $ext, $file->getClientOriginalName() );
        $name = str_replace(' ', '_', $name);



        $next = FileModel::query()->where('permalink', 'LIKE', $directory . $name . "%" )->count();
        if($next > 0){
            $name = "{$name}_{$next}";
        }


        $name_with_directory = $directory . $name . '.'. $ext;

        try{
            DB::beginTransaction();
            $file_db = FileModel::query()->create([
                'name' => $name . '.'. $ext,
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'mimetype' => $file->getClientMimeType(),
                'business' => $business->id,
                'user' => $u,
                'visibility' => $v,
                'url' => $this->getFileRoute($y, $m, $u, $v, $name . '.'. $ext),
                'permalink' => $name_with_directory
            ]);

            if($v === 'public'){
                if (!file_exists($directory)) mkdir($directory, 0777, true);
                $file->move($directory, $name . '.'. $ext);
            }

            $file_db->save();
            if($v !== 'public') Storage::putFileAs( $directory, $file, $name . '.'. $ext);

            $file_to_save = file_get_contents($directory . $name . '.'. $ext);

            $img_directory = "images/" . $y. "/". $m. "/" . $b . "/" . $u . "/" . $v . "/";

            if(str_starts_with($file->getClientMimeType(), 'video')){

                try{
                    FFMpeg::openUrl($this->getFileRoute($y, $m, $u, $v, $name . '.'. $ext))
                        ->getFrameFromSeconds(1)
                        ->export()
                        ->toDisk('default')
                        ->save($img_directory . $name . '.jpg');


                    $img = $this->saveRelatedImage($img_directory, $name, $business, $u, $v, $y, $m);

                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (\Throwable $e){

                }
            }
            if($file->getClientMimeType() == 'application/pdf'){
                try{
                    $pdf = new PdfToImage\Pdf(public_path() .'/'. $name_with_directory);
                    $pdf->setOutputFormat('jpg');
                    $pdf->saveImage(public_path() .'/'. $img_directory . '/' . $name . '.jpg');

                    $img = $this->saveRelatedImage($img_directory, $name, $business, $u, $v, $y, $m);
                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (\Throwable $e){
                    $ff = $e;
                }
            }

            $file_to_save = base64_encode($file_to_save);

            $obj = [
                'original_url' => $this->getFileRoute($y, $m, $u, $v, $name . '.'. $ext),
                'file' => $file_to_save,
                'name' => $name . '.'. $ext,
                'extension' => $file->getClientOriginalExtension(),
                'mimetype' => $file->getClientMimeType(),
                'type' => 'file',
                'email' => auth()->user()->email,
                'file_id' => $file_db->id
            ];

            syncWp($obj, 'save-file');

            DB::commit();
            return response(__('Success'), 200);
        }catch (\Throwable $e){
            DB::rollBack();

            DB::beginTransaction();
            ErrorLog::query()->create([
                'message' =>  $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::commit();

            if($v === 'public') File::delete($name_with_directory);
            if(Storage::exists($name_with_directory))  Storage::delete($name_with_directory);
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function saveRelatedImage($img_directory, $name, $business, $u, $v, $y, $m){
        if (!file_exists($img_directory)) mkdir($img_directory, 0777, true);

        $img_f = Storage::disk('default')->get($img_directory . $name . '.jpg');

        $img_file = Image::make($img_f);


        $img = ImageFile::query()->create([
            'name' => $name . '.jpg',
            'height' => $img_file->getHeight(),
            'width' => $img_file->getWidth(),
            'size' => strlen($img_f),
            'extension' => 'jpg',
            'mimetype' => 'image/jpg',
            'business' => $business->id,
            'user' => $u,
            'visibility' => $v,
            'url' => $this->getImageRoute($y, $m, $u, $v, $name . '.jpg'),
            'thumbnail' => $this->getImageRoute($y, $m, $u, $v, $name . '_thumbnail.jpg'),
            'small' => $this->getImageRoute($y, $m, $u, $v, $name . '_small.jpg'),
            'medium' => $this->getImageRoute($y, $m, $u, $v, $name . '_medium.jpg'),
            'large' => $this->getImageRoute($y, $m, $u, $v, $name . '_large.jpg'),
            'xlarge' => $this->getImageRoute($y, $m, $u, $v, $name . '_xlarge.jpg'),
            'permalink' => $img_directory . $name . '.jpg'
        ]);

        $this->imageSaving($img_f, $img_file->getWidth(), $img_file->getHeight(), $img->thumbnail,'thumbnail');
        $this->imageSaving($img_f, $img_file->getWidth(), $img_file->getHeight(), $img->small, 'small');
        $this->imageSaving($img_f, $img_file->getWidth(), $img_file->getHeight(), $img->medium, 'medium');
        $this->imageSaving($img_f, $img_file->getWidth(), $img_file->getHeight(), $img->large, 'large');
        $this->imageSaving($img_f, $img_file->getWidth(), $img_file->getHeight(), $img->xlarge,'xlarge');


        $image_to_save = file_get_contents($img_directory . $name . '.jpg');
        $image_to_save = base64_encode($image_to_save);

        $obj = [
            'original_url' => $this->getImageRoute($y, $m, $u, $v, $name . '.jpg'),
            'file' => $image_to_save,
            'name' => $name . '.jpg',
            'extension' => 'jpg',
            'mimetype' => 'image/jpg',
            'type' => 'image',
            'email' => auth()->user()->email,
            'file_id' => $img->id
        ];
        $img->save();

        syncWp($obj, 'save-file');


        return $img;
    }


    public function getImageRoute($y, $m, $u, $v, $i): string {
        return route('image.getImage',[
            'y' => $y,
            'm' =>$m,
            'b' => session(BUSINESS_IDENTIFY),
            'u' => $u,
            'v' => $v,
            'i' => $i
        ]);
    }

    function imageSaving($image, $w, $h, $name, $size = ''){
        $name = str_replace(URL::to('/').'/', '', $name);
        $sizes =  $this->calcSize($size, $h, $w);

        $h = $sizes[0];
        $w = $sizes[1];

        $image = Image::make($image)->encode('jpg')->resize($w, $h);
        $image->save(public_path($name), 100);
    }

    function calcSize($size, $h, $w): array {
        switch ($size){
            case 'thumbnail':
                $h = 180;
                $w = 180 ;
                break;
            case 'small' :
                $h = $h / 6;
                $w = $w / 6 ;
                break;
            case 'medium':
                $h = $h / 2;
                $w = $w / 2 ;
                break;
            case 'large' :
                $h = $h * 2;
                $w = $w * 2 ;
                break;
            case 'xlarge' :
                $h = $h * 3;
                $w = $w * 3 ;
                break;
        }

        $higher = $h;
        if($w > $higher) $higher = $w;
        if($higher >= 4800){
            $ratio  = $higher / 4800;
            $h = $h / $ratio;
            $w = $w / $ratio;
        }

        $smaller = $h;
        if($w < $smaller) $smaller = $w;
        if($smaller <= 180){
            $ratio = 180 / $smaller;
            $h = $h * $ratio;
            $w = $w * $ratio;
        }

        return [round($h), round($w)];
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

    public function getFileRoute($y, $m, $u, $v, $f): string {
        return route('file.getFile',[
            'y' => $y,
            'm' =>$m,
            'b' => session(BUSINESS_IDENTIFY),
            'u' => $u,
            'v' => $v,
            'f' => $f
        ]);
    }

    public function getFile(Request $request,$y, $m, $b, $u, $v, $i ) {
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

        $cond_b = $the_file->visibility == 'business' && $b !== session(BUSINESS_IDENTIFY);
        $cond_p1 = $the_file->visibility == 'private' && !Auth::check();
        $cond_p2 = $the_file->visibility == 'private' && Auth::check() && auth()->user()->getAuthIdentifier() != $u ;

        if( $cond_b || $cond_p1 || $cond_p2) {
            return response()->make($file, 200, ["Content-Type" => Storage::mimeType($default_path)]);
        }

        $file = Storage::get($the_file->permalink);

        return response()->make($file, 200, ["Content-Type" => $the_file->mimetype]);
    }

}
