<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarMedia extends Model
{
    use HasFactory;

    protected $table = 'car_media';

    protected $fillable = [
        'car_id',
        'file_path',
        'file_url',
        'media_type',
        'is_cover',
        'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
