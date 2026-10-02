<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertInertia(fn (Assert $page) => $page->component('Public/Home'));
    }

    public function test_home_page_shows_only_published_featured_memories_in_sort_order(): void
    {
        Gallery::query()->create([
            'title' => 'Kenangan kedua',
            'slug' => 'kenangan-kedua',
            'image_path' => 'yearbook/memories/kedua.jpg',
            'status' => true,
            'is_featured' => true,
            'sort_order' => 2,
        ]);
        Gallery::query()->create([
            'title' => 'Kenangan pertama',
            'slug' => 'kenangan-pertama',
            'image_path' => 'yearbook/memories/pertama.jpg',
            'status' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);
        Gallery::query()->create([
            'title' => 'Belum dipublikasikan',
            'slug' => 'belum-dipublikasikan',
            'image_path' => 'yearbook/memories/draft.jpg',
            'status' => false,
            'is_featured' => true,
            'sort_order' => 0,
        ]);
        Gallery::query()->create([
            'title' => 'Bukan unggulan',
            'slug' => 'bukan-unggulan',
            'image_path' => 'yearbook/memories/normal.jpg',
            'status' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Public/Home')
            ->has('memories', 2)
            ->where('memories.0.title', 'Kenangan pertama')
            ->where('memories.1.title', 'Kenangan kedua'));
    }
}
