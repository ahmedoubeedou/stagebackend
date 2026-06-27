<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarVideo;

class CarVideoController extends Controller
{
    // 📌 Save video path (string URL or path)
    public function store(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'video_path' => 'required|string'
        ]);

        $video = CarVideo::create([
            'car_id' => $request->car_id,
            'video_path' => $request->video_path
        ]);

        return response()->json([
            'message' => 'Video saved successfully',
            'data' => [
                'id' => $video->id,
                'car_id' => $video->car_id,
                'url' => $video->video_path
            ]
        ]);
    }

    // 📌 Get videos by car
    public function getByCar($car_id)
    {
        $videos = CarVideo::where('car_id', $car_id)->get();

        return response()->json([
            'car_id' => $car_id,
            'videos' => $videos->map(function ($vid) {
                return [
                    'id' => $vid->id,
                    'url' => $vid->video_path
                ];
            })
        ]);
    }

    // 📌 Delete video
    public function delete($id)
    {
        $video = CarVideo::findOrFail($id);
        $video->delete();

        return response()->json([
            'message' => 'Video deleted successfully'
        ]);
    }
}