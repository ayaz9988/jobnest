<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeDownloadController extends Controller
{
    public function __invoke(Resume $resume): StreamedResponse
    {
        Gate::forUser(auth('admin')->user())->authorize('download', $resume);

        $disk = Storage::disk('local');

        abort_unless($resume->hasStoredFile(), 404);

        return response()->streamDownload(function () use ($disk, $resume): void {
            $stream = $disk->readStream($resume->file_path);

            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $resume->downloadName(), [
            'Content-Type' => $resume->mime_type ?: 'application/octet-stream',
        ]);
    }
}
