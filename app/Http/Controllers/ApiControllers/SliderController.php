<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Repositories\SliderRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SliderController extends Controller
{
    private SliderRepository $sliderRepository;

    /**
     * @param SliderRepository $sliderRepository
     */
    public function __construct(SliderRepository $sliderRepository)
    {
        $this->sliderRepository = $sliderRepository;
    }

    public function all(Request $request): Response|JsonResponse  {

        $request[BUSINESS_IDENTIFY] = request()->header(BUSINESS_IDENTIFY);
        session([BUSINESS_IDENTIFY =>  $request[BUSINESS_IDENTIFY]]);

        $rq = getRequestParams($request);
        $sliders = $this->sliderRepository->search($rq->search);

        $roles = auth()->user()->roles()->get()->pluck('id')->toArray();

        $sliders = $sliders->where(function($q) use($roles){
           $q->whereNull('role')->orWhereIn('role', $roles);
        });

        $sliders = $sliders->whereHas(BUSINESS_IDENTIFY)->with(BUSINESS_IDENTIFY)->sortable();
        return response()->json(['sliders' => $sliders->get()]);
    }


}
