<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    // GET /api/cars — list with filters
    public function index(Request $request)
    {
        $query = Car::with(['media', 'user'])->where('status', 'available');

        // Resilient parameter checks (handles both snake_case and camelCase from frontend)
        $brand = $request->input('brand');
        $model = $request->input('model');
        $fuel = $request->input('fuel') ?? $request->input('fuel_type');
        $transmission = $request->input('transmission');
        $minPrice = $request->input('min_price') ?? $request->input('minPrice');
        $maxPrice = $request->input('max_price') ?? $request->input('maxPrice');
        $minYear = $request->input('min_year') ?? $request->input('minYear') ?? $request->input('year');
        $maxYear = $request->input('max_year') ?? $request->input('maxYear');
        $location = $request->input('location');
        $search = $request->input('search');

        if ($brand) {
            $query->where('brand', $brand);
        }
        if ($model) {
            $query->where('model', 'like', "%{$model}%");
        }
        if ($fuel) {
            $query->where('fuel_type', $fuel);
        }
        if ($transmission) {
            $query->where('transmission', $transmission);
        }
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('price', '>=', floatval($minPrice));
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('price', '<=', floatval($maxPrice));
        }
        if ($minYear !== null && $minYear !== '') {
            $query->where('year', '>=', intval($minYear));
        }
        if ($maxYear !== null && $maxYear !== '') {
            $query->where('year', '<=', intval($maxYear));
        }
        if ($location) {
            $query->where('location', 'like', "%{$location}%");
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Return paginated list (10 per page)
        $cars = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'data'   => $cars
        ]);
    }

    // GET /api/cars/{id} — single car with all media
    public function show($id)
    {
        $car = Car::with(['media', 'user'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $car
        ]);
    }

    // GET /api/user/cars — current user's listings
    public function myCars(Request $request)
    {
        $cars = Car::with(['media', 'user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $cars
        ]);
    }

    // POST /api/cars — create listing with media
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand'        => 'required|string|max:80',
            'model'        => 'required|string|max:80',
            'year'         => 'required|digits:4|integer',
            'price'        => 'required|numeric|min:0',
            'mileage'      => 'required|integer|min:0',
            'fuel_type'    => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:automatic,manual',
            'images'       => 'nullable|array|max:10',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'video'        => 'nullable|mimetypes:video/mp4,video/quicktime|max:51200',
            'color'        => 'nullable|string|max:40',
            'description'  => 'nullable|string',
            'location'     => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $car = Car::create([
            'user_id'      => $request->user()->id,
            'brand'        => $request->brand,
            'model'        => $request->model,
            'year'         => $request->year,
            'price'        => $request->price,
            'mileage'      => $request->mileage,
            'fuel_type'    => $request->fuel_type,
            'transmission' => $request->transmission,
            'color'        => $request->color,
            'description'  => $request->description,
            'location'     => $request->location ?? 'Nouakchott',
            'status'       => 'available',
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store('cars/images', 'public');
                $car->media()->create([
                    'file_path'  => $path,
                    'file_url'   => asset(Storage::url($path)),
                    'media_type' => 'image',
                    'is_cover'   => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        }

        // Upload video
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('cars/videos', 'public');
            $car->media()->create([
                'file_path'  => $path,
                'file_url'   => asset(Storage::url($path)),
                'media_type' => 'video',
                'is_cover'   => false,
                'sort_order' => 0,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Car listing created successfully',
            'data'    => $car->load(['media', 'user']),
        ], 201);
    }

    // PUT /api/cars/{id} — update car listing
    public function update(Request $request, $id)
    {
        $car = Car::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'brand'        => 'required|string|max:80',
            'model'        => 'required|string|max:80',
            'year'         => 'required|digits:4|integer',
            'price'        => 'required|numeric|min:0',
            'mileage'      => 'required|integer|min:0',
            'fuel_type'    => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:automatic,manual',
            // images and video validation are handled manually because they can contain string URLs
            'color'        => 'nullable|string|max:40',
            'description'  => 'nullable|string',
            'location'     => 'nullable|string|max:100',
            'status'       => 'nullable|in:available,sold,hidden',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $car->update([
            'brand'        => $request->brand,
            'model'        => $request->model,
            'year'         => $request->year,
            'price'         => $request->price,
            'mileage'      => $request->mileage,
            'fuel_type'    => $request->fuel_type,
            'transmission' => $request->transmission,
            'color'        => $request->color,
            'description'  => $request->description,
            'location'     => $request->location,
            'status'       => $request->status ?? $car->status,
        ]);

        // Handing Media files Sync (Images & Videos)
        
        // 1. Parse existing image URLs sent by frontend to keep
        $inputImages = $request->input('images') ?? [];
        $existingUrlsToKeep = [];
        foreach ($inputImages as $img) {
            if (is_string($img) && (str_starts_with($img, 'http://') || str_starts_with($img, 'https://'))) {
                $existingUrlsToKeep[] = $img;
            }
        }

        // 2. Delete any database image records not present in the frontend list
        $currentImages = $car->media()->where('media_type', 'image')->get();
        foreach ($currentImages as $currImage) {
            if (!in_array($currImage->file_url, $existingUrlsToKeep)) {
                Storage::disk('public')->delete($currImage->file_path);
                $currImage->delete();
            }
        }

        // 3. Upload new images if any
        if ($request->hasFile('images')) {
            $existingCount = $car->media()->where('media_type', 'image')->count();
            foreach ($request->file('images') as $i => $image) {
                // Validate individual files manually to prevent crashes
                if ($image->isValid() && in_array(strtolower($image->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $path = $image->store('cars/images', 'public');
                    $car->media()->create([
                        'file_path'  => $path,
                        'file_url'   => asset(Storage::url($path)),
                        'media_type' => 'image',
                        'is_cover'   => false,
                        'sort_order' => $existingCount + $i,
                    ]);
                }
            }
        }

        // 4. Recalculate cover image and sorting
        $allImages = $car->media()->where('media_type', 'image')->orderBy('sort_order')->get();
        foreach ($allImages as $index => $imgModel) {
            $imgModel->update([
                'is_cover'   => $index === 0, // first remaining image is cover
                'sort_order' => $index,
            ]);
        }

        // 5. Video Sync
        if ($request->video === 'delete') {
            // Delete video
            $currentVideos = $car->media()->where('media_type', 'video')->get();
            foreach ($currentVideos as $v) {
                Storage::disk('public')->delete($v->file_path);
                $v->delete();
            }
        } elseif ($request->hasFile('video')) {
            // Delete existing video first
            $currentVideos = $car->media()->where('media_type', 'video')->get();
            foreach ($currentVideos as $v) {
                Storage::disk('public')->delete($v->file_path);
                $v->delete();
            }

            // Upload new video
            $videoFile = $request->file('video');
            if ($videoFile->isValid()) {
                $path = $videoFile->store('cars/videos', 'public');
                $car->media()->create([
                    'file_path'  => $path,
                    'file_url'   => asset(Storage::url($path)),
                    'media_type' => 'video',
                    'is_cover'   => false,
                    'sort_order' => 0,
                ]);
            }
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Car listing updated successfully',
            'data'    => $car->load(['media', 'user']),
        ]);
    }

    // DELETE /api/cars/{id} — delete car + files
    public function destroy(Request $request, $id)
    {
        $car = Car::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Delete all files from public storage disk
        foreach ($car->media as $media) {
            Storage::disk('public')->delete($media->file_path);
        }

        // Delete car listing (cascade deletes car_media in db)
        $car->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Car listing deleted successfully'
        ]);
    }
}
