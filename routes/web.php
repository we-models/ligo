<?php

use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FontController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTypeController;
use App\Http\Controllers\RatingTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DataTypeController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\SliderController ;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ObjectTypeController ;
use App\Http\Controllers\FieldController ;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\ObjectTypeRelationController;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if(Auth::check()) return redirect(route('home', app()->getLocale()));
    return redirect(route('login', app()->getLocale()));
});

Route::get('/images/{y}/{m}/{b}/{u}/{v}/{i}', [ImageController::class, 'getImage'])->name('image.getImage');
Route::get('/files/{y}/{m}/{b}/{u}/{v}/{f}', [FileController::class, 'getFile'])->name('file.getFile');

Route::middleware(['auth'])->group(function (){
    renderRoute('/get_fb_config', HomeController::class, 'getFBConfig', 'home.fb_config', 'GET');
    renderRoute('/fcm_token', HomeController::class, 'fcmSave', 'user.fcm_Save', 'POST');
});



Route::group([
        'prefix' => '{locale}',
        'where' => ['locale' => '[a-zA-Z]{2}'],
        'middleware' => 'setlocale'
    ], function() {

    Auth::routes(['verify' => true]);

    Route::middleware(['auth'])->group(function (){



        renderRoute('business/select',         BusinessController::class,     'select' ,          'business.select',  'GET'   );
        renderRoute('business/select/{code}',         BusinessController::class,     'selectCode' ,'business.select.code',  'GET'   );

        renderRoute('user/change/password', UserController::class, 'changePassword', 'user.change_password', 'GET');
        renderRoute('user/change/password', UserController::class, 'changePasswordSave', 'user.change_password_save', 'POST');

        /** Auth routes */
        renderRoute('logout',               HomeController::class,     'logout' ,       'logout',     'GET'   );

    });

    Route::middleware(['auth', 'has.business'])->group(function () {
        /** Image routes */
        renderRoute('image/all',            ImageController::class,     'all' ,     'image.all',  'GET'   );
        renderRoute('image',                ImageController::class);

        /** Image routes */
        renderRoute('file/all',            FileController::class,     'all' ,     'file.all',  'GET'   );
        renderRoute('file',                FileController::class);

        renderRoute('home',                 HomeController::class,     'index' ,        'home',       'GET'   );

        renderRoute('notification/by_user',NotificationController::class,     'by_user' ,   'notification.by_user', 'GET'   );
        renderRoute('notification/mark_as_read',NotificationController::class,     'mark_as_read' ,   'notification.mark_as_read', 'DELETE'   );

        renderRoute('font/all',FontController::class,     'all' ,   'font.all', 'GET'   );

        renderRoute('chat/get_channels',ChatController::class,     'get_channels' ,   'chat.get_channels', 'GET'   );
        renderRoute('chat/get_chats',ChatController::class,     'get_chats' ,   'chat.get_chats', 'GET'   );
        renderRoute('chat/send',ChatController::class,     'send' ,   'chat.send', 'POST'   );
        renderRoute('chat/get_individual',ChannelController::class,     'get_individual' ,   'chat.get_individual', 'GET'   );
        renderResource('chat', ChatController::class );


    });

    Route::middleware(['auth', 'has.business', 'permissions',  'is.admin'])->group(function () {

        /** Business routes */
        renderResource('business', BusinessController::class);

        /** Role routes */
        renderRoute('role/assign/business',    RoleController::class,     'assignBusiness' ,   'role.assign_business', 'GET'   );
        renderResource('role', RoleController::class);

        /** Group routes */
        renderResource('slider', SliderController::class);

        /** Group routes */
        renderRoute('group/assign/business',GroupController::class,     'assignBusiness' ,   'group.assign_business', 'GET'   );
        renderResource('group', GroupController::class);

        /** Permission routes */
        renderRoute('permission/assign/group',PermissionController::class,     'assignGroup' ,   'permission.assign_group', 'GET'   );
        renderRoute('permission/assign/role',  PermissionController::class,     'assignRoles' ,   'permission.assign_role', 'GET'   );
        renderResource('permission', PermissionController::class);

        /** User routes */
        renderRoute('user/assign/role',     UserController::class,     'assignRoles' ,    'user.assign_role', 'GET'   );
        renderResource('user', UserController::class);

        /** Assignment routes */
        renderRoute('assign', AssignmentController::class, 'assignments' , 'assign.objects', 'GET');
        renderRoute('assign/save', AssignmentController::class, 'saveAssignment' , 'assign.save', 'POST');

        /** Configuration routes */
        renderResource('configuration', ConfigurationController::class);

        /** Type routes */
        renderResource('datatype', DataTypeController::class);

        /** System routes */
        renderResource('system', SystemConfigController::class);

        /** Object Type routes */
        renderRoute('object_type/assign/role',ObjectTypeController::class,     'assignRoles' ,   'object_type.assign_role', 'GET'   );
        renderResource('object_type', ObjectTypeController::class);

        /** Object Type Relation routes */
        renderResource('object_type_relation', ObjectTypeRelationController::class);

        /** Object routes */
        renderRoute('object/duplicate', ObjectController::class, 'duplicate', 'object.duplicate', 'POST');
        renderRoute('object/filters', ObjectController::class, 'reportFilter', 'object.filters', 'GET');
        renderRoute('object/report', ObjectController::class, 'report', 'object.report', 'GET');
        renderRoute('object/report/all', ObjectController::class, 'reportFiltered', 'object.report.filtered', 'GET');
        renderResource('object', ObjectController::class);

        /** Object routes */
        renderResource('font', FontController::class);

        renderResource('rating_type', RatingTypeController::class);

        renderRoute('comment/save', CommentController::class, 'save', 'comment.save', 'POST');
        renderRoute('comment/rating_types', CommentController::class, 'rating_types', 'comment.rating_types', 'GET');
        renderResource('comment', CommentController::class);

        /** Link routes */
        renderResource('link', LinkController::class);

        /** Object fields */
        renderResource('field', FieldController::class);

        /** Object fields */
        renderResource('channel', ChannelController::class);

        renderRoute('user/assign/channel',UserController::class,     'assignChannel' ,   'intermediary.assign_channel', 'GET'   );


        renderResource('notification_type', NotificationTypeController::class );

        renderResource('notification', NotificationController::class );


    });
});

/**
 * @param string $name
 * @param string $class
 * @return void
 */
function renderResource(string $name, string $class): void{
    renderRoute($name . '/all',      $class,     'all' ,          $name . '.all',   'GET' );
    renderRoute($name . '/logs',     $class,     'logs' ,         $name . '.logs',  'GET' );
    renderRoute($name . '/details',  $class,     'details' ,      $name . '.details', 'GET' );
    renderRoute($name, $class );
}

/**
 * @param string $url
 * @param string $controller
 * @param string|null $method
 * @param string|null $name
 * @param string|null $http_request
 * @return void
 */
function renderRoute(string $url, string $controller, string $method = null, string $name = null, string $http_request = null): void {
    switch ($http_request){
        case 'GET':
            Route::get('/'. $url,   [$controller, $method])->name($name);
            break;
        case 'POST':
            Route::post('/'. $url,   [$controller, $method])->name($name);
            break;
        case 'DELETE':
            Route::delete('/'. $url,   [$controller, $method])->name($name);
            break;
        default:
            Route::resource($url, $controller);
    }
}

