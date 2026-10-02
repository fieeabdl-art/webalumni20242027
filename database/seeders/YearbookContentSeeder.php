<?php

namespace Database\Seeders;

use App\Models\AboutContent;
use App\Models\HomeContent;
use Illuminate\Database\Seeder;

class YearbookContentSeeder extends Seeder
{
    public function run(): void
    {
        HomeContent::query()->firstOrCreate([], [
            'hero_title' => 'KITA PERNAH DI SINI.',
            'hero_subtitle' => 'Sebuah perjalanan yang tidak akan terulang lagi.',
            'cta_text' => 'JELAJAHI CERITA',
            'intro_title' => 'INI BUKAN HANYA TENTANG SIAPA KITA. INI TENTANG CERITA YANG PERNAH KITA BAGIKAN.',
            'intro_description' => 'Setiap ruang, wajah, dan momen menyimpan bagian kecil dari perjalanan bersama. Arsip ini disiapkan untuk menampung cerita yang ingin terus diingat.',
            'closing_title' => 'MUNGKIN KITA AKAN BERJALAN KE ARAH YANG BERBEDA. TAPI KITA PERNAH BERJALAN BERSAMA.',
            'closing_description' => 'TERIMA KASIH ATAS CERITANYA.',
        ]);

        AboutContent::query()->firstOrCreate([], [
            'description' => 'DATA BELUM TERSEDIA',
            'story' => 'DATA BELUM TERSEDIA',
        ]);
    }
}