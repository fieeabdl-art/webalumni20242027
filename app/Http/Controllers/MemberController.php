<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Members', [
            'members' => Member::query()->where('status', true)->orderBy('sort_order')->orderBy('name')->get(),
            'classes' => Member::query()->where('status', true)->whereNotNull('class_name')->distinct()->orderBy('class_name')->pluck('class_name'),
            'majors' => Member::query()->where('status', true)->whereNotNull('major')->distinct()->orderBy('major')->pluck('major'),
        ]);
    }
}