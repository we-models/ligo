<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ErrorLog;
use App\Repositories\ImageRepository;
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
use Intervention\Image\Facades\Image;
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

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();

        $rq = getRequestParams($request);
        $images = $this->imageRepository->search($rq->search)
            ->where('user', auth()->user()->getAuthIdentifier())
            ->where('business', $business->id);
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
    public function store(Request $request): Application|ResponseFactory|Response
    {
        set_time_limit(1800);
        $request->validate([ 'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif' ]);
        $input = $request->all();
        $image = $request->file('image');

        $y = date('Y');
        $m = date('m');
        $b = session('business');
        $u = auth()->user()->getAuthIdentifier();
        $v = in_array($input['visibility'], IMAGE_LOCATIONS) ? $input['visibility'] : 'public' ;

        $ext = $image->getClientOriginalExtension();

        $directory = "images/" . $y. "/". $m. "/" . $b . "/" . $u . "/" . $v . "/";

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
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
                'business' => $business->id,
                'user' => $u,
                'visibility' => $v,
                'url' => $this->getImageRoute($y, $m, $u, $v, $name . '.'. $ext),
                'thumbnail' => $this->getImageRoute($y, $m, $u, $v, $name . '_thumbnail.'. $ext),
                'small' => $this->getImageRoute($y, $m, $u, $v, $name . '_small.'. $ext),
                'medium' => $this->getImageRoute($y, $m, $u, $v, $name . '_medium.'. $ext),
                'large' => $this->getImageRoute($y, $m, $u, $v, $name . '_large.'. $ext),
                'xlarge' => $this->getImageRoute($y, $m, $u, $v, $name . '_xlarge.'. $ext),
                'permalink' => $name_with_directory
            ]);

            if($v === 'public'){
                if (!file_exists($directory)) mkdir($directory, 0777, true);
                $this->imageSaving($image, $input['width'], $input['height'], $img->url, $ext);
                $this->imageSaving($image, $input['width'], $input['height'], $img->thumbnail, $ext,'thumbnail');
                $this->imageSaving($image, $input['width'], $input['height'], $img->small,$ext, 'small');
                $this->imageSaving($image, $input['width'], $input['height'], $img->medium, $ext,'medium');
                $this->imageSaving($image, $input['width'], $input['height'], $img->large, $ext,'large');
                $this->imageSaving($image, $input['width'], $input['height'], $img->xlarge,$ext, 'xlarge');
            }

            $img->save();
            if($v !== 'public') Storage::putFileAs( $directory, $image, $name . '.'. $ext);

            $image_to_save = file_get_contents($request->file('image')->getPathName());
            $image_to_save = base64_encode($image_to_save);

            $obj = [
                'original_url' => $this->getImageRoute($y, $m, $u, $v, $name . '.'. $ext),
                'file' => $image_to_save,
                'name' => $name . '.'. $ext,
                'extension' => $image->getClientOriginalExtension(),
                'mimetype' => $image->getClientMimeType(),
                'type' => 'image',
                'email' => auth()->user()->email,
                'file_id' => $img->id
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

            if($v === 'public') {
                File::delete($name_with_directory);
                File::delete($directory . $name . '_xlarge.'. $ext);
                File::delete($directory . $name . '_large.'. $ext);
                File::delete($directory . $name . '_medium.'. $ext);
                File::delete($directory . $name . '_small.'. $ext);
                File::delete($directory . $name . '_thumbnail.'. $ext);
            }
            if(Storage::exists($name_with_directory))  Storage::delete($name_with_directory);
            return response($e->getMessage(), 403);
        }
    }
    public function getImage(Request $request,$y, $m, $b, $u, $v, $i ) {

        $input = $request->all();

        $default_path = 'default\\default.jpg';
        $file= Storage::get($default_path);

        $image = $request->getRequestUri();
        $image = explode('?', $image);
        $image = $image[0];
        $image = ImageFile::query()
            ->where('url', 'like', '%' . $image)
            ->orWhere('thumbnail', 'like', '%' . $image)
            ->orWhere('small', 'like', '%' . $image)
            ->orWhere('medium', 'like', '%' . $image)
            ->orWhere('large', 'like', '%' . $image)
            ->orWhere('xlarge', 'like', '%' . $image)
            ->first();

        if($image == null){
            return response()->make($file, 200, ["Content-Type" => Storage::mimeType($default_path)]);
        }
        $cond_b = $image->visibility == 'business' && $b !== session(BUSINESS_IDENTIFY);
        $cond_p1 = $image->visibility == 'private' && !Auth::check();
        $cond_p2 = $image->visibility == 'private' && Auth::check() && auth()->user()->getAuthIdentifier() != $u ;

        if( $cond_b || $cond_p1 || $cond_p2) {
            return response()->make($file, 200, ["Content-Type" => Storage::mimeType($default_path)]);
        }

        $file = Storage::get($image->permalink);
        if($image->extension == 'gif'){
            return response()->make($file, 200, ["Content-Type" => $image->mimetype]);
        }

        $sizes = $this->calcSize(($input['size'] ?? ''), $image->height,  $image->width);

        $h = $sizes[0];
        $w = $sizes[1];

        $img = Image::make($file)->resize($w, $h);
        return $img->response($image->extension);
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

    function imageSaving($image, $w, $h, $name, $ext, $size = ''){
        $name = str_replace(URL::to('/').'/', '', $name);
        $sizes =  $this->calcSize($size, $h, $w);

        $h = $sizes[0];
        $w = $sizes[1];

        $image = Image::make($image)->encode($ext)->resize($w, $h);
        $image->save($name, 100);
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
}
