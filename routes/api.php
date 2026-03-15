<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FaceRecognitionController;
use App\Http\Controllers\Api\CctvController;
use App\Http\Controllers\ChildController;

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

Route::middleware('api.token')->group(function () {
    // Face Recognition dari Raspberry Pi
    Route::post('/face-recognition', [FaceRecognitionController::class, 'store']);
    Route::get('/face-recognition/today', [FaceRecognitionController::class, 'today']);
    Route::get('/face-recognition', [FaceRecognitionController::class, 'index']);

    // CCTV dari Raspberry Pi
    Route::post('/cctv/activity', [CctvController::class, 'logActivity']);
    Route::post('/cctv/status', [CctvController::class, 'updateStatus']);
    Route::get('/cctv/cameras', [CctvController::class, 'getCameras']);

    // Sync data anak untuk training LBPH di Pi
    Route::get('/children/for-training', [ChildController::class, 'forTraining']);
});
