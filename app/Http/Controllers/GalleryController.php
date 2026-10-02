<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Memories', [
            'memories' => Gallery::query()->where('status', true)->orderBy('sort_order')->orderByDesc('date')->get(),
            'categories' => Gallery::query()->where('status', true)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }
}