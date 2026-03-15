<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vehicle extends Model
{
    use HasFactory;
    
    protected $table = 'vehicles';

    protected $fillable = [
        'name',
        'model',
        'phone',
        'car_image',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vehicle) {
            if (empty($vehicle->slug)) {
                $vehicle->slug = $vehicle->generateUniqueSlug($vehicle->name);
            }
        });

        static::updating(function ($vehicle) {
            if ($vehicle->isDirty('name') && empty($vehicle->slug)) {
                $vehicle->slug = $vehicle->generateUniqueSlug($vehicle->name);
            }
        });
    }

    public function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
