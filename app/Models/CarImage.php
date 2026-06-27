<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Car;

class CarImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'image_path',
        'is_cover',
    ];

    // الصورة تنتمي إلى سيارة واحدة
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}