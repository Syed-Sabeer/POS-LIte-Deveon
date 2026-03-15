<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;
    
    protected $table = 'services';

    protected $fillable = [
        'heading',
        'slug',
        'description',
        'image',
        'icon',
        'visibility',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = $service->generateUniqueSlug($service->heading);
            }
        });

        static::updating(function ($service) {
            if ($service->isDirty('heading') && empty($service->slug)) {
                $service->slug = $service->generateUniqueSlug($service->heading);
            }
        });
    }

    public function generateUniqueSlug($heading)
    {
        $slug = Str::slug($heading);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
