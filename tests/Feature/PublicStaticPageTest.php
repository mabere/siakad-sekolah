<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicStaticPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_static_pages_share_one_public_template(): void
    {
        $school = School::create([
            'name' => 'SMA Negeri Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $author = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-static-page@siakad.test',
            'password' => 'password',
            'school_id' => $school->id,
            'is_active' => true,
        ]);

        Page::create([
            'school_id' => $school->id,
            'author_id' => $author->id,
            'title' => 'Profil Sekolah',
            'slug' => 'profil',
            'content' => '<p>Isi profil sekolah.</p><ul><li>Program unggulan</li></ul><ol><li>Langkah pendaftaran</li></ol>',
            'status' => 'Published',
        ]);
        Page::create([
            'school_id' => $school->id,
            'author_id' => $author->id,
            'title' => 'Visi dan Misi',
            'slug' => 'visi-misi',
            'content' => '<p>Isi visi dan misi sekolah.</p>',
            'status' => 'Published',
        ]);

        $profile = $this->get('/p/profil')->assertOk();
        $vision = $this->get('/p/visi-misi')->assertOk();

        $profile->assertSee('Profil Sekolah')
            ->assertSee('Isi profil sekolah.')
            ->assertSee('prose prose-slate prose-lg max-w-none', false)
            ->assertSee('list-disc', false)
            ->assertSee('list-decimal', false)
            ->assertSee('Program unggulan')
            ->assertSee('Langkah pendaftaran');
        $vision->assertSee('Visi dan Misi')
            ->assertSee('Isi visi dan misi sekolah.')
            ->assertSee('prose prose-slate prose-lg max-w-none', false);
    }
}
