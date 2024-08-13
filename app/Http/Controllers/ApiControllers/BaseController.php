<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
// use App\Models\Business;
use App\Models\ErrorLog;
use App\Models\Field;
use App\Models\ImageFile;
use App\Repositories\ObjectRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;

class BaseController extends Controller
{
    public ObjectRepository $objectRepository;

    /**
     * @param ObjectRepository $objectRepository
     */
    public function __construct(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    public function saveManipulation($model, $status = 'created'){
        $model->manipulated_by()->attach(auth()->user()->getAuthIdentifier(), ['model_type' => get_class($model), 'type' => $status]);
    }

    /**
     * @throws GuzzleException
     * @throws \Throwable
     */
    public function saveImage($image_name, $ext, $image_size, $image_data){
        set_time_limit(1800);

        $binaryImage = base64_decode($image_data);

        $image = Image::make($binaryImage);

        $y = date('Y');
        $m = date('m');
        $b = session('business');
        $u = auth()->user()->getAuthIdentifier();
        $v = 'public' ;

        $directory = "images/" . $y. "/". $m. "/" . $b . "/" . $u . "/" . $v . "/";

        $image_name = str_replace('.' . $ext, "", $image_name);

        $business = Business::query()->where('code', session(BUSINESS_IDENTIFY))->first();
        $name = $this->getImageName($directory, $ext,  $image_name );
        $name = str_replace(' ', '_', $name);


        $name_with_directory = $directory . $name . '.'. $ext;

        try{
            DB::beginTransaction();
            $img = ImageFile::query()->create([
                'name' => $name . '.'. $ext,
                'height' => $image->height(),
                'width' => $image->width(),
                'size' => $image_size,
                'extension' => $ext,
                'mimetype' => $image->mime(),
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

            if (!file_exists($directory)) mkdir($directory, 0777, true);
            $this->imageSaving($image, $image->width(), $image->height(), $img->url, $ext);
            $this->imageSaving($image, $image->width(), $image->height(), $img->thumbnail, $ext,'thumbnail');
            $this->imageSaving($image, $image->width(), $image->height(), $img->small,$ext, 'small');
            $this->imageSaving($image, $image->width(), $image->height(), $img->medium, $ext,'medium');
            $this->imageSaving($image, $image->width(), $image->height(), $img->large, $ext,'large');
            $this->imageSaving($image, $image->width(), $image->height(), $img->xlarge,$ext, 'xlarge');


            $img->save();



            $obj = [
                'original_url' => $this->getImageRoute($y, $m, $u, $v, $name . '.'. $ext),
                'file' => $image_data,
                'name' => $name . '.'. $ext,
                'extension' => $ext,
                'mimetype' => $image->mime(),
                'type' => 'image',
                'email' => auth()->user()->email,
                'file_id' => $img->id
            ];

            // $obj = syncWp($obj, 'save-file');

            DB::commit();
            return ['image' => $img, 'wp' => $obj];
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

            if(Storage::exists($name_with_directory))  Storage::delete($name_with_directory);
            throw $e;
        }
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
