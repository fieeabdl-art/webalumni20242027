<?php

namespace App\Http\Controllers;

use App\Models\AboutContent;
use App\Models\Gallery;
use App\Models\HomeContent;
use App\Models\Member;
use App\Models\Teacher;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Home', [
            'home' => HomeContent::query()->first() ?? [],
            'about' => AboutContent::query()->first() ?? [],
            'stats' => [
                'members' => Member::query()->where('status', true)->count() ?: null,
                'classes' => Member::query()->where('status', true)->whereNotNull('class_name')->distinct()->count('class_name') ?: null,
                'teachers' => Teacher::query()->where('status', true)->count() ?: null,
                'memories' => Gallery::query()->where('status', true)->count() ?: null,
            ],
            'members' => Member::query()->where('status', true)->orderBy('sort_order')->orderBy('name')->limit(4)->get(),
            'teachers' => Teacher::query()->where('status', true)->orderBy('sort_order')->orderBy('name')->limit(3)->get(),
            'memories' => Gallery::query()->where('status', true)->where('is_featured', true)->orderBy('sort_order')->orderBy('id')->limit(6)->get(),
        ]);
    }
}