<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'content', 'attribution', 'status', 'sort_order'])]
class PageContent extends Model
{
    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}