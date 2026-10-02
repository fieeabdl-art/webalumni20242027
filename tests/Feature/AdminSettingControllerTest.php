<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_motion_preferences(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put('/admin/pengaturan', [
                'site_name' => 'Arsip Uji',
                'preloader_enabled' => false,
                'custom_cursor_enabled' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'preloader_enabled',
            'group' => 'motion',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'custom_cursor_enabled',
            'group' => 'motion',
            'value' => '1',
        ]);
    }
}
