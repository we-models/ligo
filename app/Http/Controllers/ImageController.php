<?php

namespace App\Http\Controllers;

use App\Models\ErrorLog;
use App\Repositories\ImageRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ImageFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    private ImageRepository $imageRepository;

    /**
     * @var string
     */
    public string $object = ImageFile::class;

    /**
     * @param ImageRepository $imageRepo
     */
    public function __construct(ImageRepository $imageRepo)
    {
        $this->imageRepository = $imageRepo;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): Application|Factory|View
    {
        $obj = new $this->object();
        return view('pages.general.image', ['sorts' => json_encode($obj->sortable)]);
    }

    /**
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function all(Request $request) : Response|JsonResponse {

        $rq = getRequestParams($request);
        $images = $this->imageRepository->search($rq->search)
        ->where('user', auth()->user()->getAuthIdentifier());
        if(!isset($request['sort']) || !isset($request['direction'])){
            $images = $images->orderBy('id', 'desc');
        }
        $images = $images->sortable();

        return $this->imageRepository->getResponse($images, $rq);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return ResponseFactory|Application|Response
     */
    public function store(Request $request)
    {
        set_time_limit(1800);
        $request->validate([ 'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif' ]);
        $input = $request->all();
        $image = $request->file('image');

        $y = date('Y');
        $m = date('m');
        $u = auth()->user()->getAuthIdentifier();

        $ext = $image->getClientOriginalExtension();

        $directory = "images/" . $y. "/". $m. "/" . $u . "/";

        $name = $this->getImageName($directory, $ext,  $image->getClientOriginalName() );
        $name = str_replace(' ', '_', $name);


        $name_with_directory = $directory . $name . '.'. $ext;

        try{
            DB::beginTransaction();
            $img = ImageFile::query()->create([
                'name' => $name . '.'. $ext,
                'height' => $input['height'],
                'width' => $input['width'],
                'size' => $image->getSize(),
                'extension' => $image->getClientOriginalExtension(),
                'mimetype' => $image->getClientMimeType(),
                'user' => $u,
                'url' => $this->getImageRoute($y, $m, $u, $name . '.'. $ext),
                'permalink' => $name_with_directory
            ]);
            if (!file_exists($directory)) mkdir($directory, 0777, true);
            $this->imageSaving($image, $img->url);

            $img->save();

            DB::commit();

            $img = ImageFile::query()->where('id',$img->id)->with('user')->first();

            return response()->json($img, 200);

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

            File::delete($name_with_directory);
            if(Storage::exists($name_with_directory))  Storage::delete($name_with_directory);
            return response($e->getMessage(), 403);
        }
    }

    /**
     * @throws BindingResolutionException
     */
    public function getImage(Request $request, $y, $m, $u, $i ) {

        $input = $request->all();

        $default_path = 'default\\default.jpg';
        $file= Storage::get($default_path);

        $image = $request->getRequestUri();
		//dd($input);
        $image = explode('?', $image);
        $image = $image[0];
        $image = ImageFile::query()
            ->where('url', 'like', '%' . $image)
            ->first();

        if($image == null){
            return response()->make($file, 200, ["Content-Type" => Storage::mimeType($default_path)]);
        }

        $file = Storage::get($image->permalink);
        if($image->extension == 'gif'){
            return response()->make($file, 200, ["Content-Type" => $image->mimetype]);
        }

        $img = ImageManager::imagick()->read($file);
        return response()->make($img, 200, ["Content-Type" => $image->mimetype]);
    }

    public function getImageRoute($y, $m, $u, $i): string {
        return route('image.getImage',[
            'y' => $y,
            'm' =>$m,
            'u' => $u,
            'i' => $i
        ]);
    }

    function getImageName($directory, $extension , $nm): string {
        $name = explode(".", $nm);
        if(count($name) == 1 || end($name) !== $extension) {
            $name = $nm . '.' . $extension;
            $name = explode('.', $name);
        }
        $name = implode('.', $name);
        $validateImage = explode('.',$name);
        array_pop($validateImage);
        $validateImage = array_map(function ($item){
            return cleanString($item);
        }, $validateImage);
        $validateImage = implode('.', $validateImage);
        $directory_find = str_replace('\\', '\\\\', $directory);
        $imageExists =  ImageFile::query()->where('permalink', 'like',$directory_find . $validateImage . '%')->count();
        $imageExists = ($imageExists === 0) ? '' : '_' . ($imageExists + 1);
        return $validateImage . $imageExists;
    }

    function imageSaving($image, $name): void
    {
        $name = str_replace(URL::to('/').'/', '', $name);
        $image = ImageManager::imagick()->read($image);
        $image->save($name);
    }
}
