<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\Gallery;
use App\Models\HomeContent;
use App\Models\Member;
use App\Models\PageContent;
use App\Models\Teacher;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'counts' => [
                'members' => Member::query()->count(),
                'teachers' => Teacher::query()->count(),
                'memories' => Gallery::query()->count(),
                'contents' => HomeContent::query()->count() + AboutContent::query()->count() + PageContent::query()->count(),
            ],
        ]);
    }
}