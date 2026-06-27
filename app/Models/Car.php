<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'year',
        'price',
        'mileage',
        'fuel_type',
        'transmission',
        'color',
        'description',
        'location',
        'status',
    ];

    // Belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Has many media files
    public function media()
    {
        return $this->hasMany(CarMedia::class)->orderBy('sort_order');
    }

    // Shortcut: only images
    public function images()
    {
        return $this->hasMany(CarMedia::class)->where('media_type', 'image');
    }

    // Favorites relationship
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // Override toArray to match frontend schema expectations
    public function toArray()
    {
        $array = parent::toArray();

        // Convert key timestamps
        $array['createdAt'] = $this->created_at ? $this->created_at->format('Y-m-d') : null;

        // Populate media array
        // Make sure media is loaded or query it
        $mediaCollection = $this->relationLoaded('media') ? $this->media : $this->media()->get();
        
        $array['images'] = $mediaCollection->where('media_type', 'image')->sortBy('sort_order')->pluck('file_url')->values()->toArray();
        $array['video'] = $mediaCollection->where('media_type', 'video')->first()?->file_url;

        // Populate seller
        $userModel = $this->relationLoaded('user') ? $this->user : $this->user()->first();
        if ($userModel) {
            $array['seller'] = [
                'id'    => $userModel->id,
                'name'  => $userModel->name,
                'phone' => $userModel->phone,
            ];
        } else {
            $array['seller'] = null;
        }

        return $array;
    }
}