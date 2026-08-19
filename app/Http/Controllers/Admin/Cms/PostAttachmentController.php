<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Support\CurrentSchool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostAttachmentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'attachment' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:2048',
                'dimensions:max_width=3000,max_height=3000',
            ],
        ]);

        $schoolId = app(CurrentSchool::class)->id();
        $path = $request->file('attachment')->store("posts/inline/{$schoolId}", 'public');

        if ($path === false) {
            return response()->json([
                'message' => 'Gambar gagal disimpan.',
            ], 500);
        }

        return response()->json([
            'url' => Storage::disk('public')->url($path),
        ]);
    }
}
