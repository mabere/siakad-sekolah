<?php

namespace Tests\Feature;

use App\Livewire\Admin\Cms\Post\Form;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_form_uses_rich_text_laravel_and_sanitizes_rich_content_on_save(): void
    {
        Storage::fake('public');

        $school = School::create([
            'name' => 'SMA Artikel Test',
            'level' => 'SMA',
            'status' => 'NEGERI',
            'is_active' => true,
            'is_setup_completed' => true,
        ]);
        $admin = User::create([
            'name' => 'Admin Artikel Test',
            'email' => 'admin-cms-posts@siakad.test',
            'password' => 'password',
            'school_id' => $school->id,
            'is_active' => true,
        ]);
        $admin->assignRole(Role::firstOrCreate([
            'name' => 'Admin Sekolah',
            'guard_name' => 'web',
        ]));
        $category = PostCategory::create([
            'school_id' => $school->id,
            'name' => 'Berita Sekolah',
            'slug' => 'berita-sekolah',
        ]);

        $component = Livewire::actingAs($admin)->test(Form::class)
            ->assertSee('trix-editor', false)
            ->assertSee('trix-toolbar', false)
            ->assertSee('data-trix-upload-url', false)
            ->set('title', 'Berita Sekolah')
            ->set('content', '<style>body { color: red; }</style><h2>Pengumuman</h2><ul><li>Agenda sekolah</li></ul><ol><li>Langkah pertama</li></ol><table><thead><tr><th>Agenda</th></tr></thead><tbody><tr><td>Rapat</td></tr></tbody></table><script>alert(1)</script>')
            ->set('post_category_id', $category->id)
            ->set('status', 'Published')
            ->call('save')
            ->assertRedirect(route('admin.cms.posts'));

        $component->assertHasNoErrors();

        $post = Post::query()->where('school_id', $school->id)->firstOrFail();
        $this->assertSame('Berita Sekolah', $post->title);
        $this->assertStringContainsString('<h2>Pengumuman</h2>', $post->content);
        $this->assertStringContainsString('<ul><li>Agenda sekolah</li></ul>', $post->content);
        $this->assertStringContainsString('<ol><li>Langkah pertama</li></ol>', $post->content);
        $this->assertStringContainsString('<table>', $post->content);
        $this->assertStringContainsString('Rapat', $post->content);
        $this->assertStringNotContainsString('<script', $post->content);
        $this->assertStringNotContainsString('body {', $post->content);

        $this->get('/berita/'.$post->slug)
            ->assertOk()
            ->assertSee('list-disc', false)
            ->assertSee('list-decimal', false)
            ->assertSee('Agenda sekolah')
            ->assertSee('Langkah pertama');

        $attachmentResponse = $this->actingAs($admin)
            ->withSession(['active_role' => 'Admin Sekolah'])
            ->post(route('admin.cms.posts.attachments.store'), [
                'attachment' => UploadedFile::fake()->image('inline.png'),
            ]);

        $attachmentResponse
            ->assertOk()
            ->assertJsonStructure(['url']);
        $this->assertStringStartsWith('/storage/', (string) $attachmentResponse->json('url'));

        $this->assertCount(1, Storage::disk('public')->allFiles('posts/inline'));
    }
}
