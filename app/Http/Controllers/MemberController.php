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
            'majors' => Member::query()->where('status', true)->whereNotNull('major')->distinct()->orderBy('major')->pluck('major'),
            'classes' => Member::query()->where('status', true)->whereNotNull('class_name')->distinct()->orderBy('class_name')->pluck('class_name'),
        ]);
    }

    public function show(Member $anggota): Response
    {
        abort_unless($anggota->status, 404);

        return Inertia::render('Public/Member', [
            'member' => [
                'id' => $anggota->id,
                'name' => $anggota->name,
                'nickname' => $anggota->nickname,
                'class_name' => $anggota->class_name,
                'major' => $anggota->major,
                'photo_url' => $anggota->photo_url,
                'original_url' => $anggota->original_url,
                'poster_url' => $anggota->poster_url,
                'poster_uses_cutout' => $anggota->poster_uses_cutout,
                'quote' => $anggota->quote,
                'bio' => $anggota->bio,
                'instagram' => $anggota->instagram,
            ],
        ]);
    }
}
