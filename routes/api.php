<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\CarImageController;
use App\Http\Controllers\Api\CarVideoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 🔓 بدون تسجيل دخول
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 يحتاج Token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});




// Route::get('/test', function () {

//     return response()->json([
//         "message"=>"API working"
//     ]);

// });







/*
|--------------------------------------------------------------------------
| Car API Routes
|--------------------------------------------------------------------------
| جميع الروابط الخاصة بالسيارات (Cars)
| محمية بـ Sanctum (يجب تسجيل الدخول)
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * 📌 جلب سيارات المستخدم الحالي
     */
    Route::get('/cars', [CarController::class, 'index']);

    /**
     * 📌 لوحة التحكم: جميع السيارات
     */
    Route::get('/dashboard/cars', [CarController::class, 'dashboard']);

    /**
     * 📌 إضافة سيارة جديدة
     */
    Route::post('/cars', [CarController::class, 'store']);

    /**
     * 📌 عرض سيارة واحدة
     */
    Route::get('/cars/{id}', [CarController::class, 'show']);

    /**
     * 📌 تحديث سيارة
     */
    Route::put('/cars/{id}', [CarController::class, 'update']);

    /**
     * 📌 حذف سيارة
     */
    Route::delete('/cars/{id}', [CarController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Auth required)
|--
------------------------------------------------------------------------*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------
    | 📸 Car Images
    |--------------------------
    */

    Route::post('/car-images', [CarImageController::class, 'store']);
    Route::get('/car-images/{car_id}', [CarImageController::class, 'getByCar']);
    Route::delete('/car-images/{id}', [CarImageController::class, 'delete']);

    /*
    |--------------------------
    | 🎥 Car Videos
    |--------------------------
    */

    Route::post('/car-videos', [CarVideoController::class, 'store']);
    Route::get('/car-videos/{car_id}', [CarVideoController::class, 'getByCar']);
    Route::delete('/car-videos/{id}', [CarVideoController::class, 'delete']);
});