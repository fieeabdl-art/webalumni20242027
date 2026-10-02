<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminTeacherController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => Teacher::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Teachers/Form', ['teacher' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        unset($data['photo']);
        $teacher = Teacher::query()->create($data);

        if ($photo) {
            $teacher->photo_path = $photo->store('yearbook/teachers', 'public');
            $teacher->save();
        }

        return to_route('admin.teachers.index')->with('success', 'Guru ditambahkan.');
    }

    public function edit(Teacher $guru): Response
    {
        return Inertia::render('Admin/Teachers/Form', ['teacher' => $guru]);
    }

    public function update(Request $request, Teacher $guru): RedirectResponse
    {
        $data = $this->validated($request);
        $photo = $request->file('photo');
        $oldPhoto = $guru->photo_path;
        unset($data['photo']);
        $guru->update($data);

        if ($photo) {
            $guru->photo_path = $photo->store('yearbook/teachers', 'public');
            $guru->save();
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        return to_route('admin.teachers.index')->with('success', 'Data guru diperbarui.');
    }

    public function destroy(Teacher $guru): RedirectResponse
    {
        if ($guru->photo_path) {
            Storage::disk('public')->delete($guru->photo_path);
        }
        $guru->delete();

        return back()->with('success', 'Guru dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:140'],
            'role' => ['nullable', 'string', 'max:140'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'quote' => ['nullable', 'string', 'max:1000'],
            'message' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}