<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['hero_title', 'hero_subtitle', 'hero_image', 'cta_text', 'intro_title', 'intro_description', 'closing_title', 'closing_description'])]
class HomeContent extends Model
{
    protected $appends = ['hero_image_url'];

    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->hero_image ? Storage::disk('public')->url($this->hero_image) : null;
    }
}