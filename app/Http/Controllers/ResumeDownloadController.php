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
        $path = $resume->file_path;

        abort_unless(
            filled($path)
            && str_starts_with($path, 'resumes/')
            && $disk->exists($path),
            404,
        );

        return response()->streamDownload(function () use ($disk, $path): void {
            $stream = $disk->readStream($path);

            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $resume->downloadName());
    }
}
