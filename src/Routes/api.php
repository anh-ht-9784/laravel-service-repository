<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Service Repository API Routes
|--------------------------------------------------------------------------
|
| Add your API routes for services and repositories here.
| Example:
|
| Route::prefix('api/v1')->group(function () {
|     Route::apiResource('users', UserController::class);
|     Route::apiResource('products', ProductController::class);
| });
|
*/ 