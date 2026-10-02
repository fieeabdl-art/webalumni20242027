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
            'status' => true,
            'sort_order' => 0,
        ]);

        $this->get(route('members.show', $member))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Member')
                ->where('member.name', 'Anggota Uji')
                ->where('member.class_name', 'Kelas Uji'));
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
}
