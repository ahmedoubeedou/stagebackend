<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarImage;

class CarImageController extends Controller
{
    // 📌 Save image path (string URL or path)
    public function store(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'image_path' => 'required|string'
        ]);

        $image = CarImage::create([
            'car_id' => $request->car_id,
            'image_path' => $request->image_path
        ]);

        return response()->json([
            'message' => 'Image saved successfully',
            'data' => [
                'id' => $image->id,
                'car_id' => $image->car_id,
                'url' => $image->image_path
            ]
        ]);
    }

    // 📌 Get images by car
    public function getByCar($car_id)
    {
        $images = CarImage::where('car_id', $car_id)->get();

        return response()->json([
            'car_id' => $car_id,
            'images' => $images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->image_path
                ];
            })
        ]);
    }

    // 📌 Delete image
    public function delete($id)
    {
        $image = CarImage::findOrFail($id);
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }
}