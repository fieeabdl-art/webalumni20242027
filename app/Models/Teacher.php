<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'subject', 'role', 'photo_path', 'quote', 'message', 'status', 'sort_order'])]
class Teacher extends Model
{
    protected $appends = ['photo_url'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}