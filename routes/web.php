<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\DataTypeController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IconController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\ObjectTypeController;
use App\Http\Controllers\ObjectTypeRelationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RatingTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SystemConfigController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/




if(!function_exists('renderResource')){
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

    Route::get('/', function () {
        if(Auth::check()) return redirect(route('home', app()->getLocale()));
        return redirect(route('login', app()->getLocale()));
    });

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/images/{y}/{m}/{u}/{i}', [ImageController::class, 'getImage'])->name('image.getImage');
    Route::get('/files/{y}/{m}/{u}/{f}', [FileController::class, 'getFile'])->name('file.getFile');

    Route::group([
        'prefix' => '{locale}',
        'where' => ['locale' => '[a-zA-Z]{2}'],
        'middleware' => 'setlocale'
    ], function() {

        Auth::routes(['verify' => false]);

        Route::middleware(['auth'])->group(function () {

            renderRoute('logout', HomeController::class, 'logout', 'logout', 'GET');

            /** User routes */
            renderRoute('user/assign/role',     UserController::class,     'assignRoles' ,    'user.assign_role', 'GET'   );
            renderRoute('user/profile',UserController::class , 'profile', 'user.profile','GET');
            renderRoute('user/profile',UserController::class , 'profileUpdate', 'user.profile.update','POST');
            renderRoute('user/profile/update_password',UserController::class , 'profileUpdatePassword', 'user.profile.update.password','POST');


            renderRoute('image/all',            ImageController::class,     'all' ,     'image.all',  'GET'   );
            renderRoute('image',                ImageController::class);

            /** Image routes */
            renderRoute('file/all',            FileController::class,     'all' ,     'file.all',  'GET'   );
            renderRoute('file',                FileController::class);

            renderRoute('home',                 HomeController::class,     'index' ,        'home',       'GET'   );
        });

        /** Object routes */

        Route::middleware(['auth', 'for.object'])->group(function () {
            renderResource('object', ObjectController::class);
        });

        Route::middleware(['auth', 'permissions',  'is.admin'])->group(function () {

            /** Role routes */
            renderResource('role', RoleController::class);

            /** Group routes */
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

            /** Type routes */
            renderResource('icon', IconController::class);

            /** System routes */
            renderResource('system', SystemConfigController::class);

            /** Object Type routes */
            renderRoute('object_type/assign/role',ObjectTypeController::class,     'assignRoles' ,   'object_type.assign_role', 'GET'   );
            renderResource('object_type', ObjectTypeController::class);

            /** Object Type Relation routes */
            renderResource('object_type_relation', ObjectTypeRelationController::class);

            renderResource('rating_type', RatingTypeController::class);

            /** Link routes */
            renderResource('link', LinkController::class);

            /** Object fields */
            renderResource('field', FieldController::class);

        });

    });

}
