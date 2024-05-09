<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WpControllers\ObjectTypeController;
use App\Http\Controllers\WpControllers\UserController;

Route::post('/object-types', [ObjectTypeController::class, 'all'])->name('wp.object_types');

Route::post('/save-user', [UserController::class, 'save'])->name('wp.user.save');
Route::post('/recovery', [UserController::class, 'recovery'])->name('wp.user.recovery');
Route::post('/reset', [UserController::class, 'reset'])->name('wp.user.reset');
