<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminGalleryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Memories/Index', [
            'memories' => Gallery::query()->orderBy('sort_order')->orderByDesc('date')->get(),
            'categories' => Gallery::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Memories/Form', ['memory' => null, 'categories' => $this->categories()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $image = $request->file('image');
        unset($data['image']);
        $memory = Gallery::query()->create($data);
        $memory->image_path = $image->store('yearbook/memories', 'public');
        $memory->save();

        return to_route('admin.memories.index')->with('success', 'Kenangan ditambahkan.');
    }

    public function edit(Gallery $kenangan): Response
    {
        return Inertia::render('Admin/Memories/Form', ['memory' => $kenangan, 'categories' => $this->categories()]);
    }

    public function update(Request $request, Gallery $kenangan): RedirectResponse
    {
        $data = $this->validated($request, $kenangan);
        $image = $request->file('image');
        $oldImage = $kenangan->image_path;
        unset($data['image']);
        $kenangan->update($data);

        if ($image) {
            $kenangan->image_path = $image->store('yearbook/memories', 'public');
            $kenangan->save();
            Storage::disk('public')->delete($oldImage);
        }

        return to_route('admin.memories.index')->with('success', 'Kenangan diperbarui.');
    }

    public function destroy(Gallery $kenangan): RedirectResponse
    {
        Storage::disk('public')->delete($kenangan->image_path);
        $kenangan->delete();

        return back()->with('success', 'Kenangan dihapus.');
    }

    private function validated(Request $request, ?Gallery $memory = null): array
    {
        $slug = Rule::unique('galleries', 'slug');
        if ($memory) {
            $slug->ignore($memory->id);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:200', $slug],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => [$memory ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'category' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_featured' => ['required', 'boolean'],
            'status' => ['required', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['slug']);

        return $data;
    }

    private function categories(): array
    {
        return Gallery::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}