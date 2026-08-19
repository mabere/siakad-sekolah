<?php

namespace App\Services;

use App\Models\PpdbRequirement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class PpdbFileService
{
    /** @var array<string, list<string>> */
    private const MIME_TYPES = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];

    /** @return array<int, string> */
    public function rules(?PpdbRequirement $requirement = null, bool $required = true): array
    {
        $extensions = $this->extensionsFor($requirement);
        $maxSize = $requirement !== null
            ? min(max(1, $requirement->max_file_size_kb), $this->globalMaxSize())
            : $this->globalMaxSize();

        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:'.implode(',', $extensions),
            'max:'.$maxSize,
        ];
    }

    /** @return array{path: string, original_name: string, mime_type: string, file_size: int} */
    public function store(UploadedFile $file, string $directory, ?PpdbRequirement $requirement = null): array
    {
        $this->assertSafeFile($file, $requirement);

        // Livewire moves a temporary upload when the destination uses the same disk.
        // Capture metadata before that move so it is not read from a no-longer-existing
        // temporary path afterward.
        $originalName = Str::limit(basename($file->getClientOriginalName()), 255, '');
        $mimeType = strtolower((string) ($file->getMimeType() ?: 'application/octet-stream'));
        $fileSize = (int) ($file->getSize() ?: 0);

        $path = $file->store($this->safeDirectory($directory), 'local');
        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages(['file' => 'Berkas gagal disimpan. Silakan coba lagi.']);
        }

        return [
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path === null || ! str_starts_with($path, 'ppdb/')) {
            return;
        }

        Storage::disk('local')->delete($path);
    }

    /** @return list<string> */
    public function extensionsFor(?PpdbRequirement $requirement = null): array
    {
        $configured = $requirement?->accepted_mimes ?: implode(',', config('ppdb.uploads.allowed_extensions', []));
        $extensions = collect(explode(',', (string) $configured))
            ->map(fn (string $value): string => strtolower(trim($value)))
            ->map(fn (string $value): string => match ($value) {
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                default => $value,
            })
            ->filter(fn (string $value): bool => array_key_exists($value, self::MIME_TYPES))
            ->unique()
            ->values()
            ->all();

        return $extensions !== [] ? $extensions : config('ppdb.uploads.allowed_extensions', ['pdf', 'jpg', 'jpeg', 'png']);
    }

    private function assertSafeFile(UploadedFile $file, ?PpdbRequirement $requirement): void
    {
        $extensions = $this->extensionsFor($requirement);
        $extension = strtolower((string) ($file->guessExtension() ?: $file->getClientOriginalExtension()));
        $mimeType = strtolower((string) ($file->getMimeType() ?: ''));
        $allowedMimes = collect($extensions)->flatMap(fn (string $value): array => self::MIME_TYPES[$value] ?? [])->all();
        $maxSize = $requirement !== null
            ? min(max(1, $requirement->max_file_size_kb), $this->globalMaxSize())
            : $this->globalMaxSize();

        if (! in_array($extension, $extensions, true) || ! in_array($mimeType, $allowedMimes, true)) {
            throw ValidationException::withMessages(['file' => 'Format berkas tidak diizinkan.']);
        }

        if ((int) ($file->getSize() ?: 0) > $maxSize * 1024) {
            throw ValidationException::withMessages(['file' => 'Ukuran berkas melebihi batas yang diizinkan.']);
        }

        $this->scanWithClamav($file);
    }

    private function scanWithClamav(UploadedFile $file): void
    {
        if (! (bool) config('ppdb.uploads.clamav.enabled', false)) {
            return;
        }

        $realPath = $file->getRealPath();
        if (! is_string($realPath) || $realPath === '') {
            throw ValidationException::withMessages(['file' => 'Berkas tidak dapat dipindai.']);
        }

        $process = new Process([
            (string) config('ppdb.uploads.clamav.binary', 'clamdscan'),
            '--no-summary',
            '--infected',
            $realPath,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw ValidationException::withMessages(['file' => 'Berkas gagal melewati pemeriksaan keamanan.']);
        }
    }

    private function safeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if ($directory === '' || ! str_starts_with($directory, 'ppdb/')) {
            throw new \InvalidArgumentException('Direktori upload PPDB tidak valid.');
        }

        return $directory;
    }

    private function globalMaxSize(): int
    {
        return max(1, (int) config('ppdb.uploads.max_file_size_kb', 10240));
    }
}
