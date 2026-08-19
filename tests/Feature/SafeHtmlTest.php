<?php

namespace Tests\Feature;

use App\Support\HostRelativeUrl;
use App\Support\SafeHtml;
use Tests\TestCase;

class SafeHtmlTest extends TestCase
{
    public function test_internal_storage_urls_are_host_relative_when_rendering_existing_content(): void
    {
        config(['app.url' => 'https://siakad-sekolah.test']);

        $html = SafeHtml::clean(
            '<p><img src="https://siakad-sekolah.test/storage/posts/inline/old.png?size=small" alt="Gambar"></p>'
        );

        $this->assertStringContainsString('src="/storage/posts/inline/old.png?size=small"', $html);
        $this->assertStringNotContainsString('siakad-sekolah.test', $html);
    }

    public function test_external_image_urls_are_not_rewritten(): void
    {
        config(['app.url' => 'https://siakad-sekolah.test']);

        $html = SafeHtml::clean('<img src="https://images.example.test/photo.png" alt="Gambar">');

        $this->assertStringContainsString('src="https://images.example.test/photo.png"', $html);
    }

    public function test_internal_menu_urls_are_host_relative(): void
    {
        config(['app.url' => 'https://siakad-sekolah.test']);

        $this->assertSame(
            '/p/visi-dan-misi?preview=1#section',
            HostRelativeUrl::normalize('https://siakad-sekolah.test/p/visi-dan-misi?preview=1#section'),
        );
        $this->assertSame(
            'https://www.example.test/berita',
            HostRelativeUrl::normalize('https://www.example.test/berita'),
        );
    }

    public function test_format_human_text_converts_markdown_elements_safely(): void
    {
        $input = "### Judul Bagian\n**Poin Penting:** Siswa melakukan *observasi* mandiri.\n- Tugas 1\n- Tugas 2";
        $formatted = SafeHtml::formatHumanText($input);

        $this->assertStringNotContainsString('###', $formatted);
        $this->assertStringNotContainsString('**Poin', $formatted);
        $this->assertStringContainsString('<strong>Poin Penting:</strong>', $formatted);
        $this->assertStringContainsString('<em>observasi</em>', $formatted);
        $this->assertStringContainsString('• Tugas 1', $formatted);
        $this->assertStringContainsString('<br', $formatted);
    }

    public function test_strip_markdown_removes_syntax(): void
    {
        $input = "### Judul\n**Teks Tebal** dan *Miring* serta `code`\n- Butir A";
        $stripped = SafeHtml::stripMarkdown($input);

        $this->assertSame("Judul\nTeks Tebal dan Miring serta code\nButir A", $stripped);
    }
}
