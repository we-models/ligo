<?php

namespace App\Http\Controllers;

use App\Models\FCMToken;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 *
 */
class HomeController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(Request $request): Renderable {
        return view('home');
    }

    /**
     * @param Request $request
     * @return Redirector|Application|RedirectResponse
     *
     * When the user call to logout the current business is removed
     */
    public function logout(Request $request): Redirector|Application|RedirectResponse {
        $request->session()->forget(BUSINESS_IDENTIFY);
        Auth::logout();
        return redirect(route('login', app()->getLocale()));
    }

    public function getFBConfig(Request $request){
        $fb_config = [
            'apiKey'=> getConfigValue('GOOGLE_FIREBASE_API_KEY'),
            'authDomain'=> getConfigValue('GOOGLE_FIREBASE_AUTH_DOMAIN'),
            'projectId'=> getConfigValue('GOOGLE_FIREBASE_PROJECT_ID'),
            'storageBucket'=> getConfigValue('GOOGLE_FIREBASE_STORAGE_BUCKET'),
            'messagingSenderId'=> getConfigValue('GOOGLE_FIREBASE_MESSAGING_SENDER_ID'),
            'appId'=> getConfigValue('GOOGLE_FIREBASE_APP_ID'),
            'measurementId'=> getConfigValue('GOOGLE_FIREBASE_MEASUREMENT_ID'),
            'auth' => Auth::check(),
            'fb_enable' => getConfigValue('GOOGLE_FIREBASE_ENABLE'),
            'fb_web_key' => getConfigValue('GOOGLE_FIREBASE_WEB')
        ];

        return response()->json($fb_config);
    }

    public function fcmSave(Request $request){
        $input = $request->all();

        $fcm = FCMToken::query()->where(['user' => auth()->user()->getAuthIdentifier(), 'device' => $input['device']])->first();
        if(!empty($fcm)){
            $fcm->token = $input['fcm_token'];
            $fcm->save();
        }else{
            FCMToken::query()->create([
                'user' => auth()->user()->getAuthIdentifier(),
                'device' => $input['device'],
                'token' => $input['fcm_token']
            ]);
        }

        return response()->json(['OK' => true]);
    }

}
