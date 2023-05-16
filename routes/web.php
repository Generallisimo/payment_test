<?php

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/', function(){
    return redirect()->route('payment');
});

// создаем роутеры для оплаты 
// роутер с объеденением запросов
Route::match(['GET', 'POST'], '/payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])
    ->name('payment_callback ');
// роутер для создания
Route::post('/payment/create', [App\Http\Controllers\PaymentController::class, 'create'])
    ->name('payment_create');
// роутер для главной страницы
Route::get('/payment', [\App\Http\Controllers\PaymentController::class, 'index'])
    ->name('payment');