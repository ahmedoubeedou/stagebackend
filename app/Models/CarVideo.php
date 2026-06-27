<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Car;

class CarVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'video_path',
        'duration',
    ];

    // الفيديو ينتمي إلى سيارة واحدة
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}