<?php

namespace Tests\Unit;

use App\Services\PpdbFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PpdbFileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_captures_metadata_before_moving_the_file(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('bukti-pembayaran.pdf', 128, 'application/pdf');

        $stored = app(PpdbFileService::class)->store($file, 'ppdb/1/1');

        $this->assertSame('bukti-pembayaran.pdf', $stored['original_name']);
        $this->assertSame('application/pdf', $stored['mime_type']);
        $this->assertSame(128 * 1024, $stored['file_size']);
        Storage::disk('local')->assertExists($stored['path']);
    }
}
