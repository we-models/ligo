<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImageController;
use App\Models\Business;
use App\Models\ErrorLog;
use App\Models\Field;
use App\Models\File as FileModel;
use App\Models\ImageFile;
use App\Repositories\FileRepository;
use App\Repositories\ImageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Spatie\PdfToImage;

class ImageFileController extends Controller
{
    private ImageRepository $imageRepository;
    private FileRepository $fileRepository;

    /**
     * @param ImageRepository $imageRepository
     * @param FileRepository $fileRepository
     */
    public function __construct(ImageRepository $imageRepository, FileRepository $fileRepository)
    {
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;
    }

    public function files(Request $request){
        $input = $request->all();
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $mimeTypes = MIMES;
        $search = $input['search'] ?? "";

        if(isset($input['field'])){
            $field = Field::query()->where('slug', $input['field'])->first();
            if($field == null) return response()->json(["error" => __('field_not_exists')], status: 401);
            $mimeTypes = empty($field->accept)? MIMES : explode(',', $field->accept);
            $mimeTypes = array_map(fn($mt)=>trim($mt), $mimeTypes);
        }
        return FileModel::query()->whereIn('mimetype', $mimeTypes)
            ->where('name', 'LIKE', "%$search%")
            ->where('user', auth()->user()->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->whereHas(BUSINESS_IDENTIFY)->paginate(10);
    }

    public function images(Request $request){
        $input = $request->all();
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $search = $input['search'] ?? "";
        return ImageFile::query()
            ->where('name', 'LIKE', "%$search%")
            ->where('user', auth()->user()->getAuthIdentifier())
            ->orderByDesc('created_at')
            ->whereHas(BUSINESS_IDENTIFY)->paginate(10);
    }


    public function getImage(Request $request,$y, $m, $b, $u, $v, $i){

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $imgCtrl = new ImageController($this->imageRepository);
        return $imgCtrl->getImage($request, $y, $m, $b, $u, $v, $i);
    }

    public function getFile(Request $request,$y, $m, $b, $u, $v, $i){

    }

    public function imageStore(Request $request){
        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);


        if(!isset($request['field'])) return response()->json(["error" => __("Field not allowed")], status: 401);

        $field = Field::query()->where('slug',$request['field'])->first();

        if($field == null) return response()->json(["error" => __("Field not valid")], status: 401);

        set_time_limit(1800);
        $request->validate([ 'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif' ]);

        $image = $request->file('image');

        $y = date('Y');
        $m = date('m');
        $b = session('business');
        $u = auth()->user()->getAuthIdentifier();
        $v = 'public' ;

        $ext = $image->getClientOriginalExtension();

        $directory = "images/" . $y. "/". $m. "/" . $b . "/" . $u . "/" . $v . "/";
        $imageCtrl = new ImageController($this->imageRepository);
        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
        $name = $imageCtrl->getImageName($directory, $ext, $image->getClientOriginalName()  );
        $name = str_replace(' ', '_', $name);
        $name_with_directory = $directory . $name . '.'. $ext;
        $theImage = Image::make($image);
        try{
            DB::beginTransaction();
            $img = ImageFile::query()->create([
                'name' => $name . '.'. $ext,
                'height' =>$theImage->getHeight(),
                'width' => $theImage->getWidth(),
                'size' => $image->getSize(),
                'extension' => $image->getClientOriginalExtension(),
                'mimetype' => $image->getClientMimeType(),
                'business' => $business->id,
                'user' => $u,
                'visibility' => $v,
                'url' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '.'. $ext),
                'thumbnail' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '_thumbnail.'. $ext),
                'small' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '_small.'. $ext),
                'medium' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '_medium.'. $ext),
                'large' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '_large.'. $ext),
                'xlarge' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '_xlarge.'. $ext),
                'permalink' => $name_with_directory
            ]);

            if (!file_exists($directory)) mkdir($directory, 0777, true);
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->url, $ext);
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->thumbnail, $ext,'thumbnail');
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->small,$ext, 'small');
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->medium, $ext,'medium');
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->large, $ext,'large');
            $imageCtrl->imageSaving($image, $theImage->getWidth(), $theImage->getHeight(), $img->xlarge,$ext, 'xlarge');

            $img->save();

            $image_to_save = file_get_contents($request->file('image')->getPathName());
            $image_to_save = base64_encode($image_to_save);

            $obj = [
                'original_url' => $imageCtrl->getImageRoute($y, $m, $u, $v, $name . '.'. $ext),
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
            return response()->json($img);
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
            File::delete($directory . $name . '_xlarge.'. $ext);
            File::delete($directory . $name . '_large.'. $ext);
            File::delete($directory . $name . '_medium.'. $ext);
            File::delete($directory . $name . '_small.'. $ext);
            File::delete($directory . $name . '_thumbnail.'. $ext);

            return response()->json(["error" => $e->getMessage()], status: 401);
        }

    }

    public function fileStore(Request $request): \Illuminate\Http\JsonResponse
    {

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $input = $request->all();

        if(!isset($input['field'])) return response()->json(["error" => __("Field not allowed")], status: 401);

        $field = Field::query()->where('slug',$request['field'])->first();

        if($field == null) return response()->json(["error" => __("Field not valid")], status: 401);

        $mimes = explode(',', $field->accept);
        $mimes = array_map(fn ($mime) => trim($mime), $mimes);

        if(count($mimes) == 0 || $field->accept  == null){
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
        $v = 'public' ;

        $ext = $file->getClientOriginalExtension();

        $directory = 'files/' . $y. '/'. $m. '/' . $b . '/' . $u . '/' . $v . '/';

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();

        $fileCtrl = new FileController($this->fileRepository);


        $name = $fileCtrl->getFileName($directory, $ext, $file->getClientOriginalName() );
        $name = str_replace(' ', '_', $name);

        $next = FileModel::query()->where('permalink', 'LIKE', $directory . $name . "%" )->count();
        if($next > 0){
            $name = "{$name}_{$next}";
        }
        $name_with_directory = $directory . $name . '.'. $ext;

        try {
            DB::beginTransaction();
            $file_db = FileModel::query()->create([
                'name' => $name . '.'. $ext,
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'mimetype' => $file->getClientMimeType(),
                'business' => $business->id,
                'user' => $u,
                'visibility' => $v,
                'url' => $fileCtrl->getFileRoute($y, $m, $u, $v, $name . '.'. $ext),
                'permalink' => $name_with_directory
            ]);

            if (!file_exists($directory)) mkdir($directory, 0777, true);
            $file->move($directory, $name . '.'. $ext);
            $file_db->save();
            $file_to_save = file_get_contents($directory . $name . '.'. $ext);

            $img_directory = "images/" . $y. "/". $m. "/" . $b . "/" . $u . "/" . $v . "/";
            if (!file_exists($img_directory)) mkdir($img_directory, 0777, true);

            if(str_starts_with($file->getClientMimeType(), 'video')){

                try{
                    FFMpeg::openUrl($fileCtrl->getFileRoute($y, $m, $u, $v, $name . '.'. $ext))
                        ->getFrameFromSeconds(1)
                        ->export()
                        ->toDisk('default')
                        ->save($img_directory . $name . '.jpg');


                    $img = $fileCtrl->saveRelatedImage($img_directory, $name, $business, $u, $v, $y, $m);

                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (\Throwable $e){
                    $r =98;
                }
            }
            if($file->getClientMimeType() == 'application/pdf'){
                try{
                    $pdf = new PdfToImage\Pdf(public_path() .'/'. $name_with_directory);
                    $pdf->setOutputFormat('jpg');
                    $pdf->saveImage(public_path() .'/'. $img_directory . '/' . $name . '.jpg');

                    $img = $fileCtrl->saveRelatedImage($img_directory, $name, $business, $u, $v, $y, $m);
                    $file_db->images()->syncWithPivotValues($img->id, ['model_type' => FileModel::class]);
                }catch (\Throwable $e){

                }
            }

            $file_to_save = base64_encode($file_to_save);

            $obj = [
                'original_url' => $fileCtrl->getFileRoute($y, $m, $u, $v, $name . '.'. $ext),
                'file' => $file_to_save,
                'name' => $name . '.'. $ext,
                'extension' => $file->getClientOriginalExtension(),
                'mimetype' => $file->getClientMimeType(),
                'type' => 'file',
                'email' => auth()->user()->email,
                'file_id' => $file_db->id
            ];

            syncWp($obj, 'save-file');

            $file_db = FileModel::query()->where('id', $file_db['id'])->with('images')->first();

            DB::commit();
            return response()->json($file_db);


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

            return response()->json(["error" => $e->getMessage()], status: 401);
        }
    }

}
