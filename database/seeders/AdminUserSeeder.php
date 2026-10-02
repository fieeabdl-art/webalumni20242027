<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('Admin not created. Set ADMIN_EMAIL and ADMIN_PASSWORD before seeding to create one.');

            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Pengurus Angkatan'),
                'password' => $password,
                'is_admin' => true,
            ],
        );
    }
}