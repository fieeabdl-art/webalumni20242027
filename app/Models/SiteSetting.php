<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'group', 'value'])]
class SiteSetting extends Model
{
    public static function publicValues(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }
}