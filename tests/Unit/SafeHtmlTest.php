<?php

namespace Tests\Unit;

use App\Support\SafeHtml;
use PHPUnit\Framework\TestCase;

class SafeHtmlTest extends TestCase
{
    public function test_cms_html_removes_scripts_event_handlers_and_unsafe_urls(): void
    {
        $html = '<p onclick="alert(1)"><b>Tebal</b> <strong>Aman</strong></p>'
            .'<script>alert(2)</script>'
            .'<a href="javascript:alert(3)">Bahaya</a>'
            .'<a href="https://example.test" target="_blank">Tautan</a>';

        $clean = SafeHtml::clean($html);

        $this->assertStringContainsString('<b>Tebal</b>', $clean);
        $this->assertStringContainsString('<strong>Aman</strong>', $clean);
        $this->assertStringContainsString('https://example.test', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
        $this->assertStringNotContainsString('<script', strtolower($clean));
        $this->assertStringNotContainsString('alert(2)', $clean);
        $this->assertStringNotContainsString('body {', $clean);
        $this->assertStringNotContainsString('onclick', strtolower($clean));
        $this->assertStringNotContainsString('javascript:', strtolower($clean));
    }
}
