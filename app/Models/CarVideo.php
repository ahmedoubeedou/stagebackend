<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'video_path'
    ];

    // 📌 relation avec Car
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}