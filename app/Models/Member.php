<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'nickname', 'class_name', 'major', 'photo_path', 'photo_cutout', 'quote', 'bio', 'instagram', 'status', 'sort_order'])]
class Member extends Model
{
    public const MAJORS = ['PPLG', 'TKR', 'AKL', 'RPL'];

    protected $appends = ['photo_url', 'photo_cutout_url'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    public function getPhotoCutoutUrlAttribute(): ?string
    {
        return $this->photo_cutout ? asset('storage/'.$this->photo_cutout) : null;
    }
}
