<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminSettingController extends Controller
{
    private const TEXT_SETTINGS = ['site_name', 'email', 'instagram', 'whatsapp', 'footer_text'];

    private const BOOLEAN_SETTINGS = ['preloader_enabled', 'custom_cursor_enabled'];

    public function edit(): Response
    {
        return Inertia::render('Admin/Settings', [
            'settings' => SiteSetting::publicValues(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'site_name' => ['required', 'string', 'max:140'],
            'email' => ['nullable', 'email', 'max:180'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'footer_text' => ['nullable', 'string', 'max:240'],
            'preloader_enabled' => ['required', 'boolean'],
            'custom_cursor_enabled' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,webp', 'max:1024'],
        ];
        $validated = $request->validate($rules);

        foreach (self::TEXT_SETTINGS as $key) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => 'general', 'value' => $validated[$key] ?? null],
            );
        }

        foreach (self::BOOLEAN_SETTINGS as $key) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => 'motion', 'value' => $validated[$key] ? '1' : '0'],
            );
        }

        foreach (['logo', 'favicon'] as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $setting = SiteSetting::query()->firstOrNew(['key' => $key]);
            $oldPath = $setting->value;
            $setting->group = 'branding';
            $setting->value = $request->file($key)->store('yearbook/branding', 'public');
            $setting->save();

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        return back()->with('success', 'Pengaturan website diperbarui.');
    }
}
