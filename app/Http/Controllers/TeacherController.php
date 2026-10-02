<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Teachers', [
            'teachers' => Teacher::query()->where('status', true)->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }
}