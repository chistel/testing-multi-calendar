<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Integration\CalendarController;
use App\Http\Controllers\Integration\OauthController;
use App\Http\Controllers\Integration\WebhookController;
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
    return view('auth.login');
});

Auth::routes();

Route::group(['middleware' => 'auth'], static function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::group(['prefix' => 'external-accounts', 'as' => 'external-account.'], function () {
        Route::get('redirect/{provider}', [OauthController::class, 'redirectToProvider'])->name('add');
        Route::get('callback/{provider}', [OauthController::class, 'callBack'])->name('callback');
        Route::get('/', [OauthController::class, 'list'])->name('list');
        Route::delete('remove-{provider}', [OauthController::class, 'disconnect'])->name('disconnect');
    });
    Route::group(['prefix' => 'calender', 'as' => 'calender.'], static function () {
        Route::get('services', [CalendarController::class, 'list'])->name('services');
        //Route::get('/', [HomeController::class,'calender'])->name('landing');
        //Route::get('json', [HomeController::class,'calenderJson'])->name('json');
    });
});
