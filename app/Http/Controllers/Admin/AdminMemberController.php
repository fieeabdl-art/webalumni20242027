<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminMemberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Members/Index', [
            'members' => Member::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Members/Form', ['member' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        unset($data['photo']);
        $member = Member::query()->create($data);

        if ($photo) {
            $member->photo_path = $photo->store('yearbook/members', 'public');
            $member->save();
        }

        return to_route('admin.members.index')->with('success', 'Anggota ditambahkan.');
    }

    public function edit(Member $anggota): Response
    {
        return Inertia::render('Admin/Members/Form', ['member' => $anggota]);
    }

    public function update(Request $request, Member $anggota): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        $oldPhoto = $anggota->photo_path;
        unset($data['photo']);
        $anggota->update($data);

        if ($photo) {
            $anggota->photo_path = $photo->store('yearbook/members', 'public');
            $anggota->save();
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        return to_route('admin.members.index')->with('success', 'Data anggota diperbarui.');
    }

    public function destroy(Member $anggota): RedirectResponse
    {
        if ($anggota->photo_path) {
            Storage::disk('public')->delete($anggota->photo_path);
        }
        $anggota->delete();

        return back()->with('success', 'Anggota dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:140'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'quote' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}