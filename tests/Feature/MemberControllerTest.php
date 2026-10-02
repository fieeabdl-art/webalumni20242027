<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_member_details_are_rendered(): void
    {
        $member = Member::query()->create([
            'name' => 'Anggota Uji',
            'class_name' => 'Kelas Uji',
            'major' => 'Jurusan Uji',
            'photo_path' => 'yearbook/members/original.webp',
            'photo_cutout' => 'yearbook/members/cutouts/poster.png',
            'status' => true,
            'sort_order' => 0,
        ]);

        $this->get(route('members.show', $member))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Member')
                ->where('member.name', 'Anggota Uji')
                ->where('member.class_name', 'Kelas Uji')
                ->where('member.original_url', asset('storage/yearbook/members/original.webp'))
                ->where('member.poster_url', asset('storage/yearbook/members/cutouts/poster.png'))
                ->where('member.poster_uses_cutout', true));
    }

    public function test_draft_member_details_return_not_found(): void
    {
        $member = Member::query()->create([
            'name' => 'Anggota Draft',
            'status' => false,
            'sort_order' => 0,
        ]);

        $this->get(route('members.show', $member))->assertNotFound();
    }

    public function test_member_archive_keeps_legacy_class_data_and_filters_by_major_values(): void
    {
        Member::query()->create([
            'name' => 'Anggota Arsip',
            'class_name' => 'Kelas Lama',
            'major' => 'rpl',
            'status' => true,
            'sort_order' => 0,
        ]);

        $this->get(route('members'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Members')
                ->where('majors.0', 'rpl')
                ->where('classes.0', 'Kelas Lama')
                ->where('members.0.class_name', 'Kelas Lama')
                ->where('members.0.major', 'rpl')
                ->where('members.0.photo_cutout_url', null)
                ->where('members.0.original_url', null)
                ->where('members.0.poster_url', null)
                ->where('members.0.poster_uses_cutout', false));
    }

    public function test_home_member_preview_includes_the_public_cutout_image_url(): void
    {
        Member::query()->create([
            'name' => 'Anggota Poster',
            'major' => 'RPL',
            'photo_path' => 'yearbook/members/member.webp',
            'photo_cutout' => 'yearbook/members/cutouts/member.png',
            'status' => true,
            'sort_order' => 0,
        ]);

        $this->get(route('home'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Home')
                ->where('members.0.photo_cutout_url', asset('storage/yearbook/members/cutouts/member.png'))
                ->where('members.0.original_url', asset('storage/yearbook/members/member.webp'))
                ->where('members.0.poster_url', asset('storage/yearbook/members/cutouts/member.png'))
                ->where('members.0.poster_uses_cutout', true));
    }
}
