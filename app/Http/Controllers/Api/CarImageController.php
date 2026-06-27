<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarImage;
use Illuminate\Http\Request;

class CarImageController extends Controller
{
    /**
     * 📌 إضافة صورة لسيارة
     */
    public function store(Request $request, $carId)
    {
        // التحقق من الصورة
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // التأكد أن السيارة تخص المستخدم
        $car = Car::where('id', $carId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Voiture introuvable ou non autorisée'
            ], 404);
        }

        // رفع الصورة
        $path = $request->file('image')->store('cars/images', 'public');

        // حفظ في قاعدة البيانات
        $image = CarImage::create([
            'car_id' => $car->id,
            'path' => $path
        ]);

        return response()->json([
            'message' => 'Image ajoutée avec succès',
            'image' => $image
        ]);
    }

    /**
     * 📌 حذف صورة
     */
    public function destroy(Request $request, $id)
    {
        // البحث عن الصورة
        $image = CarImage::find($id);

        if (!$image) {
            return response()->json([
                'message' => 'Image introuvable'
            ], 404);
        }

        // التأكد أن السيارة تخص المستخدم
        $car = Car::where('id', $image->car_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        // حذف الصورة
        $image->delete();

        return response()->json([
            'message' => 'Image supprimée avec succès'
        ]);
    }
}