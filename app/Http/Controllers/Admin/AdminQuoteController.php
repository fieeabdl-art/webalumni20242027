<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminQuoteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Quotes', [
            'quotes' => PageContent::query()->orderBy('sort_order')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        PageContent::query()->create($this->validated($request));

        return back()->with('success', 'Kata-kata ditambahkan.');
    }

    public function update(Request $request, PageContent $quote): RedirectResponse
    {
        $quote->update($this->validated($request));

        return back()->with('success', 'Kata-kata diperbarui.');
    }

    public function destroy(PageContent $quote): RedirectResponse
    {
        $quote->delete();

        return back()->with('success', 'Kata-kata dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:quote,message,memory,closing'],
            'content' => ['required', 'string', 'max:2000'],
            'attribution' => ['nullable', 'string', 'max:180'],
            'status' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);
    }
}