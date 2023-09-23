<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/users',[UserController::class,'createUser']);
Route::post('/login',[UserController::class,'login']);

Route::get('/',[UserController::class,'profile']);

Route::get('/deposit', [TransactionController::class, 'getDeposit']);
Route::post('/deposit', [TransactionController::class, 'postDeposit']);

Route::get('/withdrawal', [TransactionController::class, 'getWithdrawal']);
Route::post('/withdrawal', [TransactionController::class, 'postWithdrawal']);