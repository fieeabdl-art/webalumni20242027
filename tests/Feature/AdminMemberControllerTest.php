<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    public static function validMajors(): array
    {
        return [
            'PPLG' => ['PPLG'],
            'TKR' => ['TKR'],
            'AKL' => ['AKL'],
            'RPL' => ['RPL'],
        ];
    }

    #[DataProvider('validMajors')]
    public function test_admin_can_create_a_member_with_each_supported_major(string $major): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->post(route('admin.members.store'), [
                'name' => 'Anggota Uji',
                'major' => $major,
                'status' => true,
                'sort_order' => 0,
            ])
            ->assertRedirect(route('admin.members.index'));

        $this->assertDatabaseHas('members', [
            'name' => 'Anggota Uji',
            'major' => $major,
            'class_name' => null,
        ]);
    }

    public function test_member_major_is_required_and_cannot_be_outside_the_supported_choices(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = ['name' => 'Anggota Uji', 'status' => true, 'sort_order' => 0];

        $this->actingAs($admin)
            ->post(route('admin.members.store'), $payload)
            ->assertSessionHasErrors('major');

        $this->post(route('admin.members.store'), [...$payload, 'major' => 'Jurusan Lain'])
            ->assertSessionHasErrors('major');
    }

    public function test_member_edit_form_receives_supported_majors_and_keeps_existing_data(): void
    {
        $member = Member::query()->create([
            'name' => 'Anggota Lama',
            'class_name' => 'Kelas Lama',
            'major' => 'rpl',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.members.edit', $member))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Members/Form')
                ->where('member.class_name', 'Kelas Lama')
                ->where('member.major', 'rpl')
                ->where('majors', Member::MAJORS));
    }

    public function test_member_index_exposes_cutout_status_for_each_member(): void
    {
        Member::query()->create([
            'name' => 'Anggota Dengan Cutout',
            'photo_cutout' => 'yearbook/members/cutouts/ada.png',
        ]);
        Member::query()->create(['name' => 'Anggota Tanpa Cutout']);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.members.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Members/Index')
                ->where('members.0.poster_uses_cutout', true)
                ->where('members.1.poster_uses_cutout', false));
    }

    public function test_updating_a_legacy_member_preserves_its_class_column(): void
    {
        $member = Member::query()->create([
            'name' => 'Anggota Lama',
            'class_name' => 'Kelas Lama',
            'major' => 'rpl',
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.members.update', $member), [
                'name' => 'Anggota Lama Diperbarui',
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
            ])
            ->assertRedirect(route('admin.members.index'));

        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Anggota Lama Diperbarui',
            'class_name' => 'Kelas Lama',
            'major' => 'RPL',
        ]);
    }

    public function test_admin_can_upload_a_valid_png_cutout_for_a_member(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $cutout = $this->pngUpload('cutout.png');

        $response = $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Anggota Cutout',
            'major' => 'RPL',
            'status' => true,
            'sort_order' => 0,
            'photo_cutout' => $cutout,
        ]);

        $response->assertRedirect(route('admin.members.index'));

        $member = Member::query()->where('name', 'Anggota Cutout')->firstOrFail();
        Storage::disk('public')->assertExists($member->photo_cutout);
        $this->assertStringStartsWith('http', $member->photo_cutout_url);
        $this->assertNull($member->original_url);
        $this->assertSame($member->photo_cutout_url, $member->poster_url);
        $this->assertTrue($member->poster_uses_cutout);
    }

    public function test_admin_can_save_an_original_photo_and_cutout_together(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Anggota Dua Foto',
            'major' => 'RPL',
            'status' => true,
            'sort_order' => 0,
            'photo' => $this->pngUpload('original.png'),
            'photo_cutout' => $this->pngUpload('cutout.png'),
        ]);

        $response->assertRedirect(route('admin.members.index'));

        $member = Member::query()->where('name', 'Anggota Dua Foto')->firstOrFail();
        Storage::disk('public')->assertExists($member->photo_path);
        Storage::disk('public')->assertExists($member->photo_cutout);
        $this->assertSame(asset('storage/'.$member->photo_path), $member->original_url);
        $this->assertSame(asset('storage/'.$member->photo_cutout), $member->poster_url);
        $this->assertTrue($member->poster_uses_cutout);
    }

    public function test_admin_can_upload_an_original_photo_without_a_cutout(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Anggota Foto Asli',
            'major' => 'RPL',
            'status' => true,
            'sort_order' => 0,
            'photo' => $this->pngUpload('original.png'),
        ])->assertRedirect(route('admin.members.index'));

        $member = Member::query()->where('name', 'Anggota Foto Asli')->firstOrFail();
        Storage::disk('public')->assertExists($member->photo_path);
        $this->assertNull($member->photo_cutout);
        $this->assertSame($member->original_url, $member->poster_url);
        $this->assertFalse($member->poster_uses_cutout);
    }

    public function test_original_photo_upload_rejects_an_invalid_mime_type(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Anggota MIME Salah',
            'major' => 'RPL',
            'status' => true,
            'sort_order' => 0,
            'photo' => $this->pngUpload('original.png')->mimeType('image/gif'),
        ])->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('members', ['name' => 'Anggota MIME Salah']);
        $this->assertSame([], Storage::disk('public')->allFiles('yearbook/members'));
    }

    public function test_original_photo_upload_rejects_images_over_five_megabytes(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.members.store'), [
            'name' => 'Anggota Foto Besar',
            'major' => 'RPL',
            'status' => true,
            'sort_order' => 0,
            'photo' => $this->pngUpload('original.png')->size(5121),
        ])->assertSessionHasErrors('photo');

        $this->assertDatabaseMissing('members', ['name' => 'Anggota Foto Besar']);
        $this->assertSame([], Storage::disk('public')->allFiles('yearbook/members'));
    }

    public function test_member_cutout_upload_rejects_a_file_with_an_invalid_mime_type(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.members.store'), [
                'name' => 'Anggota Tidak Valid',
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'photo_cutout' => $this->pngUpload('cutout.png')->mimeType('image/jpeg'),
            ])
            ->assertSessionHasErrors('photo_cutout');

        $this->assertDatabaseMissing('members', ['name' => 'Anggota Tidak Valid']);
        $this->assertSame([], Storage::disk('public')->allFiles('yearbook/members/cutouts'));
    }

    public function test_member_cutout_upload_rejects_images_over_five_megabytes(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.members.store'), [
                'name' => 'Anggota Terlalu Besar',
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'photo_cutout' => $this->pngUpload('large-cutout.png')->size(5121),
            ])
            ->assertSessionHasErrors('photo_cutout');

        $this->assertDatabaseMissing('members', ['name' => 'Anggota Terlalu Besar']);
        $this->assertSame([], Storage::disk('public')->allFiles('yearbook/members/cutouts'));
    }

    public function test_non_admin_cannot_upload_a_member_cutout(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->post(route('admin.members.store'), [
                'name' => 'Anggota Terlarang',
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'photo' => $this->pngUpload('original.png'),
                'photo_cutout' => $this->pngUpload('cutout.png'),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('members', ['name' => 'Anggota Terlarang']);
        $this->assertSame([], Storage::disk('public')->allFiles('yearbook/members'));
    }

    public function test_replacing_a_member_cutout_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $oldPath = 'yearbook/members/cutouts/old.png';
        Storage::disk('public')->put($oldPath, 'old cutout');
        $member = Member::query()->create([
            'name' => 'Anggota Lama',
            'major' => 'RPL',
            'photo_cutout' => $oldPath,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.members.update', $member), [
                'name' => $member->name,
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'photo_cutout' => $this->pngUpload('new-cutout.png'),
            ])
            ->assertRedirect(route('admin.members.index'));

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($member->fresh()->photo_cutout);
    }

    public function test_replacing_a_member_original_photo_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $oldPath = 'yearbook/members/old.png';
        Storage::disk('public')->put($oldPath, 'old photo');
        $member = Member::query()->create([
            'name' => 'Anggota Foto',
            'major' => 'RPL',
            'photo_path' => $oldPath,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.members.update', $member), [
                'name' => $member->name,
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'photo' => $this->pngUpload('new-photo.png'),
            ])
            ->assertRedirect(route('admin.members.index'));

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($member->fresh()->photo_path);
    }

    public function test_removing_a_member_original_photo_deletes_the_file_and_clears_its_path(): void
    {
        Storage::fake('public');
        $oldPath = 'yearbook/members/old.png';
        Storage::disk('public')->put($oldPath, 'old photo');
        $member = Member::query()->create([
            'name' => 'Anggota Tanpa Foto',
            'major' => 'RPL',
            'photo_path' => $oldPath,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.members.update', $member), [
                'name' => $member->name,
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'remove_photo' => true,
            ])
            ->assertRedirect(route('admin.members.index'));

        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNull($member->fresh()->photo_path);
    }

    public function test_removing_a_member_cutout_deletes_the_file_and_clears_its_path(): void
    {
        Storage::fake('public');
        $oldPath = 'yearbook/members/cutouts/old.png';
        Storage::disk('public')->put($oldPath, 'old cutout');
        $member = Member::query()->create([
            'name' => 'Anggota Tanpa Cutout',
            'major' => 'RPL',
            'photo_cutout' => $oldPath,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->put(route('admin.members.update', $member), [
                'name' => $member->name,
                'major' => 'RPL',
                'status' => true,
                'sort_order' => 0,
                'remove_photo_cutout' => true,
            ])
            ->assertRedirect(route('admin.members.index'));

        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNull($member->fresh()->photo_cutout);
    }

    public function test_deleting_a_member_removes_both_photo_files(): void
    {
        Storage::fake('public');
        $originalPath = 'yearbook/members/original.png';
        $cutoutPath = 'yearbook/members/cutouts/cutout.png';
        Storage::disk('public')->put($originalPath, 'original');
        Storage::disk('public')->put($cutoutPath, 'cutout');
        $member = Member::query()->create([
            'name' => 'Anggota Dihapus',
            'photo_path' => $originalPath,
            'photo_cutout' => $cutoutPath,
        ]);

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->delete(route('admin.members.destroy', $member))
            ->assertRedirect();

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertMissing($cutoutPath);
    }

    private function pngUpload(string $filename): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC');

        return UploadedFile::fake()->createWithContent($filename, $png);
    }
}
