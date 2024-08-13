<?php

// use App\Http\Controllers\ApiControllers\ChatController;
use App\Http\Controllers\ApiControllers\ImageFileController;
use App\Http\Controllers\ApiControllers\ObjectController;
use App\Http\Controllers\ApiControllers\GoogleMapsController;
use App\Http\Controllers\ApiControllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\BusinessController as BC;
// use \App\Http\Controllers\ApiControllers\NotificationController;
// use \App\Http\Controllers\ApiControllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Auth::routes(['verify' => true]);

Auth::routes(['verify' => true]);

Route::prefix('/user')->group(function (){
    Route::post('/login', [UserController::class, 'login'])->name('api.user.login');
    Route::post('/register', [UserController::class, 'register'])->name('api.user.register');
    Route::post('/recovery', [UserController::class, 'recovery'])->name('api.user.recovery');
    Route::post('/password/reset', [UserController::class, 'reset'])->name('api.user.reset.form');


    Route::middleware(['auth:api', 'verified'])->group(function (){
        Route::get('/logout', [UserController::class, 'logout'])->name('api.user.logout');
        Route::get('/is-logged', [UserController::class, 'is_logged'])->name('api.user.is_logged');
        Route::get('/{id}', [UserController::class, 'show'])->name('api.user.show');
        Route::get('/profile', [UserController::class, 'profile'])->name('api.user.profile');
        Route::post('/profile', [UserController::class, 'profileUpdate'])->name('api.user.profile');

    });
});

Route::middleware(['auth:api', 'verified'])->group(function (){
    Route::get('/images/{y}/{m}/{b}/{u}/{v}/{i}', [ImageFileController::class, 'getImage'])->name('api.image.getImage');
    Route::get('/files/{y}/{m}/{b}/{u}/{v}/{f}', [ImageFileController::class, 'getFile'])->name('api.file.getFile');

    Route::post('/file/store', [ImageFileController::class, 'fileStore'])->name('api.file.fileStore');
    Route::post('/image/store', [ImageFileController::class, 'imageStore'])->name('api.file.imageStore');

    Route::get('/files', [ImageFileController::class, 'files'])->name('api.file.all');
    Route::get('/images', [ImageFileController::class, 'images'])->name('api.image.all');

    Route::get('/object/all', [ObjectController::class, 'all'])->name('api.object.all');
    Route::get('/object/tabs', [ObjectController::class, 'getTabs'])->name('api.object.tabs');
    Route::get('/object/new', [ObjectController::class, 'getNew'])->name('api.object.new');
    Route::post('/object/store', [ObjectController::class, 'store'])->name('api.object.store');
    Route::post('object/paymentez', [ObjectController::class, 'paymentezMethodCard'])->name('api.paymentez.card');
    Route::get('/object/available', [ObjectController::class, 'getAvailableTerms'] )->name('api.object.available');
    Route::get('/object/{id}', [ObjectController::class, 'show'])->name('api.object.show');
    Route::delete('/object/{id}',   [ObjectController::class, 'destroy'])->name('api.object.delete');


    Route::prefix('/maps')->group(function (){
        Route::post('/predictions', [GoogleMapsController::class, 'getPredictions']);
        Route::post('/place-details', [GoogleMapsController::class, 'getPlaceDetailsByPlaceId']);
    });

    // Route::get('/comment/all', [CommentController::class, 'all'])->name('api.comment.all');
    // Route::get('comment/rating_types', [CommentController::class, 'rating_types'])->name('api.comment.rating_types');

    // Route::post('/init-chat', [ChatController::class, 'initChat'])->name('api.chat.init');
    // Route::get('/chat-rooms', [ChatController::class, 'getChatRooms'])->name('api.chat.rooms');
    // Route::post('/send-chat', [ChatController::class, 'sendChat'])->name('api.chat.send');

    // Route::get('/notification/all', [NotificationController::class, 'all'])->name('api.notification.all');
    // Route::get('/notification/unreads', [NotificationController::class, 'unReads'])->name('api.notification.unreads');
    // Route::post('/notification/fcm', [NotificationController::class, 'saveFcm'])->name('api.notification.saveFcm');
    // Route::delete('/notification/read', [NotificationController::class, 'read'])->name('api.notification.read');


});

// Route::get('/information/{business}', [BC::class, 'information'])->name('business.information');

// Route::get('/object-preview/{id}', [ObjectController::class, 'preview'])->name('object.preview');
