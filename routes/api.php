<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\ProductController;

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

Route::get('auth-err',function() {

    return response( array(
        "status" => "error",
        "message" => "Usuario no admitido." ), 400);

})->name('auth-err');

Route::group(['prefix' => 'store'],function(){


    Route::resource('category', CategoryController::class);
    Route::resource('promotion', PromotionController::class);
    Route::resource('product', PromotionController::class);

    Route::group(['prefix' => 'administrator'],function(){

    Route::post('login', [ AdministratorController::class, 'login']);

        Route::group( ['middleware' => ['auth:administrator']], function(){

                Route::post('register', [AdministratorController::class, 'register']);
                Route::get('logout', [AdministratorController::class, 'logout']);
                Route::get('get-administrator', [AdministratorController::class, 'adminInfo']);
                Route::get('get-clients', [ClientController::class, 'index']);

        });
    });

    Route::group(['prefix' => 'client'],function(){

        Route::post('register', [ClientController::class, 'register']);
        Route::post('register-fb-google', [ClientController::class, 'register_login_fb_google']);
        Route::post('login', [ ClientController::class, 'login']);
        Route::post('reset-password-request', [ClientController::class, 'sendPasswordResetEmail']);
        Route::post('change-password', [ClientController::class, 'passwordResetProcess']);
        Route::get('email/verify/{id}', [ClientController::class, 'verify'])->name('verification.verify'); // Make sure to keep this as your route name
        Route::get('email/resend', [ClientController::class, 'resend'])->name('verification.resend');

            Route::group( ['middleware' => ['auth:client' ]], function(){

                Route::get('logout', [ClientController::class, 'logout']);
                Route::get('get-client', [ClientController::class, 'clientInfo']);
                Route::put('update', [ClientController::class, 'update']);

                Route::resource('direction', DirectionController::class);
                Route::get('get-directions/{id}', [DirectionController::class, 'directionsByClient']);

            });
    });
});
