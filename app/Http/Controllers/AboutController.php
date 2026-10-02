<?php

namespace App\Http\Controllers;

use App\Models\AboutContent;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/About', [
            'about' => AboutContent::query()->first() ?? [],
        ]);
    }
}