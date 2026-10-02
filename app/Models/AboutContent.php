<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['major', 'description', 'story', 'main_image'])]
class AboutContent extends Model
{
    protected $appends = ['main_image_url'];

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->main_image ? Storage::disk('public')->url($this->main_image) : null;
    }
}