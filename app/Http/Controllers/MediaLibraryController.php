<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = Str::lower((string) $request->query('search', ''));
        $disk = Storage::disk('public');

        $items = collect($disk->files('media-library'))
            ->filter(function (string $path) use ($search): bool {
                return $search === '' || Str::contains(Str::lower(basename($path)), $search);
            })
            ->map(fn (string $path): array => $this->mediaItem($path))
            ->values()
            ->all();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $path = $request->file('file')->store('media-library', 'public');

        return response()->json([
            'data' => $this->mediaItem($path),
        ], 201);
    }

    /**
     * @return array{name: string, url: string, size: int, mime_type: string, type: string}
     */
    private function mediaItem(string $path): array
    {
        $disk = Storage::disk('public');
        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';

        return [
            'name' => basename($path),
            'url' => $disk->url($path),
            'size' => $disk->size($path),
            'mime_type' => $mimeType,
            'type' => Str::before($mimeType, '/'),
        ];
    }
}
