<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\CheckoutController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// ✅ Product Routes
Route::get('/products', [ProductController::class, 'index']);
Route::post('/select-product', [ProductController::class, 'selectProduct']);

//Checkout page
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');


// ✅ Questionnaire Page
Route::get('/questionnaire', [QuestionnaireController::class, 'show']);

// ✅ Questionnaire Form Submission
//Route::post('/submit-questionnaire', [QuestionnaireController::class, 'store']);
