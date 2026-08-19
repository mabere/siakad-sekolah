<?php

namespace Tests\Feature;

use App\Livewire\Admin\Cms\Pages\Index;
use App\Models\Page;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CmsPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_form_provides_rich_text_editor_and_sanitizes_content_on_save(): void
    {
        $school = School::create([
            'name' => 'SMA CMS Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $admin = User::create([
            'name' => 'Admin CMS Test',
            'email' => 'admin-cms-pages@siakad.test',
            'password' => 'password',
            'school_id' => $school->id,
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)->test(Index::class)
            ->call('openForm')
            ->assertSee('contenteditable="true"', false)
            ->assertSee('formatBlock', false)
            ->set('title', 'Profil Sekolah')
            ->set('slug', 'profil-sekolah')
            ->set('content', '<style>body { color: red; }</style><h2>Profil</h2><p><strong>Selamat datang.</strong></p><ul><li>Persyaratan</li></ul><ol><li>Daftar</li></ol><script>alert(1)</script>')
            ->set('status', 'Draft')
            ->call('save');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas('pages', [
            'school_id' => $school->id,
            'slug' => 'profil-sekolah',
            'status' => 'Draft',
        ]);
        $content = (string) Page::query()
            ->where('school_id', $school->id)
            ->where('slug', 'profil-sekolah')
            ->value('content');

        $this->assertStringContainsString('<strong>Selamat datang.</strong>', $content);
        $this->assertStringContainsString('<ul><li>Persyaratan</li></ul>', $content);
        $this->assertStringContainsString('<ol><li>Daftar</li></ol>', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('body {', $content);
    }
}
