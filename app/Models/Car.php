<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CarImage;
use App\Models\CarVideo;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand',
        'model',
        'year',
        'price',
        'fuel_type',
        'transmission',
        'mileage',
        'color',
        'condition',
        'status',
        'description',
    ];

    /*
    |--------------------------------------
    | العلاقات
    |--------------------------------------
    */

    // السيارة تنتمي لمستخدم واحد
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // السيارة لديها صور متعددة
    public function images()
    {
        return $this->hasMany(CarImage::class);
    }

    // السيارة لديها فيديوهات متعددة
    public function videos()
    {
        return $this->hasMany(CarVideo::class);
    }

    // صورة الغلاف فقط (اختياري لكنه مهم)
    public function coverImage()
    {
        return $this->hasOne(CarImage::class)->where('is_cover', true);
    }
}