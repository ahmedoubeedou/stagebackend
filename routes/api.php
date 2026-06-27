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
| 📸 Car Images Routes
|--------------------------------------------------------------------------
| هذا الجزء مسؤول عن رفع وحذف صور السيارات
| كل العمليات محمية بـ Sanctum (يجب تسجيل الدخول)
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * 📌 إضافة صورة لسيارة معينة
     * POST /api/cars/{carId}/images
     */
    Route::post('/cars/{carId}/images', [CarImageController::class, 'store']);

    /**
     * 📌 حذف صورة سيارة
     * DELETE /api/images/{id}
     */
    Route::delete('/images/{id}', [CarImageController::class, 'destroy']);

});






/*
|--------------------------------------------------------------------------
| 🎥 Car Videos Routes
|--------------------------------------------------------------------------
| هذا الجزء مسؤول عن رفع وحذف فيديوهات السيارات
| محمي بـ Sanctum (auth)
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * 📌 إضافة فيديو إلى سيارة
     * POST /api/cars/{carId}/videos
     */
    Route::post('/cars/{carId}/videos', [CarVideoController::class, 'store']);

    /**
     * 📌 حذف فيديو
     * DELETE /api/videos/{id}
     */
    Route::delete('/videos/{id}', [CarVideoController::class, 'destroy']);

});