<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;

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

//retrieve logged in user
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'status' => true,
        'user' => $request->user()
    ], 200);
});

//route for Task resource: store, index, show, update, delete
Route::apiResource('tasks', TaskController::class);

//authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::get('logout',    [AuthController::class, 'logout']);
Route::get('refresh',   [AuthController::class, 'refresh']);

//fallback route
Route::fallback(function(){
    return response()->json([
        'status' => false,
        'message' => 'Invalid Endpoint'
    ], 404);
});
