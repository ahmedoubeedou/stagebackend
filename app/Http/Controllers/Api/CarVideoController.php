<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarVideo;
use Illuminate\Http\Request;

class CarVideoController extends Controller
{
    /**
     * 📌 إضافة فيديو لسيارة
     */
    public function store(Request $request, $carId)
    {
        // التحقق من الملف
        $request->validate([
            'video' => 'required|mimes:mp4,mov,avi,wmv|max:20000',
        ]);

        // التأكد من ملكية السيارة
        $car = Car::where('id', $carId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Voiture introuvable ou non autorisée'
            ], 404);
        }

        // رفع الفيديو
        $path = $request->file('video')->store('cars/videos', 'public');

        // حفظ في قاعدة البيانات
        $video = CarVideo::create([
            'car_id' => $car->id,
            'path' => $path
        ]);

        return response()->json([
            'message' => 'Vidéo ajoutée avec succès',
            'video' => $video
        ]);
    }

    /**
     * 📌 حذف فيديو
     */
    public function destroy(Request $request, $id)
    {
        // البحث عن الفيديو
        $video = CarVideo::find($id);

        if (!$video) {
            return response()->json([
                'message' => 'Vidéo introuvable'
            ], 404);
        }

        // التأكد من ملكية السيارة
        $car = Car::where('id', $video->car_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Non autorisé'
            ], 403);
        }

        // حذف الفيديو
        $video->delete();

        return response()->json([
            'message' => 'Vidéo supprimée avec succès'
        ]);
    }
}