<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

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
        return Inertia::render('Admin/Members/Form', [
            'member' => null,
            'majors' => Member::MAJORS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        $cutout = $request->file('photo_cutout');
        unset($data['photo'], $data['photo_cutout'], $data['remove_photo'], $data['remove_photo_cutout']);
        $member = Member::query()->create($data);

        if ($photo) {
            $member->photo_path = $this->storeImage($photo, 'yearbook/members');
        }

        if ($cutout) {
            $member->photo_cutout = $this->storeImage($cutout, 'yearbook/members/cutouts');
        }

        $member->save();

        return to_route('admin.members.index')->with('success', 'Anggota ditambahkan.');
    }

    public function edit(Member $anggota): Response
    {
        return Inertia::render('Admin/Members/Form', [
            'member' => $anggota,
            'majors' => Member::MAJORS,
        ]);
    }

    public function update(Request $request, Member $anggota): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        $cutout = $request->file('photo_cutout');
        $oldPhoto = $anggota->photo_path;
        $oldCutout = $anggota->photo_cutout;
        $removePhoto = ! $photo && $request->boolean('remove_photo');
        $removeCutout = ! $cutout && $request->boolean('remove_photo_cutout');
        unset($data['photo'], $data['photo_cutout'], $data['remove_photo'], $data['remove_photo_cutout']);

        if ($photo) {
            $anggota->photo_path = $this->storeImage($photo, 'yearbook/members');
        } elseif ($removePhoto) {
            $anggota->photo_path = null;
        }

        if ($cutout) {
            $anggota->photo_cutout = $this->storeImage($cutout, 'yearbook/members/cutouts');
        } elseif ($removeCutout) {
            $anggota->photo_cutout = null;
        }

        $anggota->fill($data)->save();

        if (($photo || $removePhoto) && $oldPhoto) {
            $this->deleteImage($oldPhoto);
        }

        if (($cutout || $removeCutout) && $oldCutout) {
            $this->deleteImage($oldCutout);
        }

        return to_route('admin.members.index')->with('success', 'Data anggota diperbarui.');
    }

    public function destroy(Member $anggota): RedirectResponse
    {
        $this->deleteImage($anggota->photo_path);
        $this->deleteImage($anggota->photo_cutout);
        $anggota->delete();

        return back()->with('success', 'Anggota dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'class_name' => ['nullable', 'string', 'max:100'],
            'major' => ['required', 'string', Rule::in(Member::MAJORS)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_cutout' => ['nullable', 'image', 'mimes:png,webp', 'max:5120'],
            'remove_photo' => ['sometimes', 'boolean'],
            'remove_photo_cutout' => ['sometimes', 'boolean'],
            'quote' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }

    private function storeImage(UploadedFile $image, string $directory): string
    {
        $path = $image->store($directory, 'public');

        if ($path === false) {
            throw new RuntimeException('The member image could not be stored.');
        }

        return $path;
    }

    private function deleteImage(?string $path): void
    {
        if ($path !== null && ! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('The member image could not be removed from storage.');
        }
    }
}
