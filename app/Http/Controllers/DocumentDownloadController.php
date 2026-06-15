<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $path         = (string) $request->query('path', '');
        $originalName = (string) $request->query('name', '');

        if (
            $path === ''
            || ! str_starts_with($path, 'products/documents/')
            || str_contains($path, '..')
        ) {
            abort(403);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $filename = $originalName !== '' ? $originalName : basename($path);
        $filename = preg_replace('/[\x00-\x1F\x7F]/', '', $filename);
        $mime     = $disk->mimeType($path) ?: 'application/octet-stream';

        return response()->streamDownload(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            while (! feof($stream)) {
                echo fread($stream, 1024 * 64);
                flush();
            }
            fclose($stream);
        }, $filename, [
            'Content-Type'        => $mime,
            'Content-Length'      => $disk->size($path),
            'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"; '
                                   . "filename*=UTF-8''" . rawurlencode($filename),
            'Cache-Control'       => 'private, no-cache',
        ]);
    }
}