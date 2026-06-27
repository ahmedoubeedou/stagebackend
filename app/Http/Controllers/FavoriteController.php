<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // GET /api/user/favorites — list user's favorite cars
    public function index(Request $request)
    {
        // Eager load cars with media and user
        $favorites = Favorite::with(['car.media', 'car.user'])
            ->where('user_id', $request->user()->id)
            ->get();

        // Extract the Car models from favorites
        $cars = $favorites->pluck('car')->filter()->values();

        return response()->json([
            'status' => 'success',
            'data'   => $cars
        ]);
    }

    // POST /api/user/favorites/{carId} — add to favorites
    public function store(Request $request, $carId)
    {
        // Check if car exists
        Car::findOrFail($carId);

        // Add to favorites if not already there
        $request->user()->favorites()->firstOrCreate([
            'car_id' => $carId
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Car added to favorites successfully'
        ]);
    }

    // DELETE /api/user/favorites/{carId} — remove from favorites
    public function destroy(Request $request, $carId)
    {
        $request->user()->favorites()
            ->where('car_id', $carId)
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Car removed from favorites successfully'
        ]);
    }

    // GET /api/user/favorites/{carId} — check if car is favorited
    public function show(Request $request, $carId)
    {
        $isFav = $request->user()->favorites()
            ->where('car_id', $carId)
            ->exists();

        return response()->json([
            'status' => 'success',
            'data'   => $isFav
        ]);
    }
}
