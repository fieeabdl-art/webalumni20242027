<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'slug', 'description', 'image_path', 'category', 'date', 'sort_order', 'is_featured', 'status'])]
class Gallery extends Model
{
    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return ['date' => 'date', 'is_featured' => 'boolean', 'status' => 'boolean'];
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}