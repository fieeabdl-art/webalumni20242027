<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminHomeController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Home', [
            'content' => HomeContent::query()->first() ?? new HomeContent(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:180'],
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cta_text' => ['required', 'string', 'max:80'],
            'intro_title' => ['required', 'string', 'max:500'],
            'intro_description' => ['required', 'string', 'max:5000'],
            'closing_title' => ['required', 'string', 'max:500'],
            'closing_description' => ['required', 'string', 'max:5000'],
        ]);

        $content = HomeContent::query()->firstOrNew();
        $oldImage = $content->hero_image;
        unset($validated['hero_image']);
        $content->fill($validated);

        if ($request->hasFile('hero_image')) {
            $content->hero_image = $request->file('hero_image')->store('yearbook/home', 'public');
        }

        $content->save();

        if ($oldImage && $content->hero_image !== $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with('success', 'Konten beranda diperbarui.');
    }
}