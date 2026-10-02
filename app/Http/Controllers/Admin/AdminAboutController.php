<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutContent;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminAboutController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/About', [
            'about' => AboutContent::query()->first() ?? new AboutContent(),
            'settings' => SiteSetting::publicValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cohort_name' => ['required', 'string', 'max:120'],
            'school_name' => ['required', 'string', 'max:180'],
            'cohort_year' => ['required', 'string', 'max:30'],
            'major' => ['nullable', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:2000'],
            'story' => ['required', 'string', 'max:12000'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $about = AboutContent::query()->firstOrNew();
        $oldImage = $about->main_image;
        $image = $request->file('main_image');
        unset($validated['cohort_name'], $validated['school_name'], $validated['cohort_year'], $validated['main_image']);
        $about->fill($validated);

        if ($image) {
            $about->main_image = $image->store('yearbook/about', 'public');
        }

        $about->save();

        foreach (['cohort_name', 'school_name', 'cohort_year'] as $key) {
            SiteSetting::query()->updateOrCreate(['key' => $key], ['group' => 'identity', 'value' => $request->string($key)->toString()]);
        }

        if ($oldImage && $about->main_image !== $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with('success', 'Identitas dan cerita angkatan diperbarui.');
    }
}