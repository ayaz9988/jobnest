<?php

use App\Models\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
Route::middleware(['auth'])->group(function () {

    Route::livewire('profile', 'pages::profile.edit')->name('profile.show');

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('jobs', 'pages::jobs.index')->name('jobs.index');

    Route::livewire('jobs/saved', 'pages::jobs.saved')->name('jobs.saved');

    Route::livewire('jobs/{jobListing}', 'pages::jobs.show')->name('jobs.show');

    Route::livewire('applications', 'pages::applications.index')->name('applications.index');

    Route::livewire('applications/{application}', 'pages::applications.show')->name('applications.show');

    Route::get('resumes/{resume}/download', function (Resume $resume) {
        abort_unless($resume->user_id === Auth::id(), 403);
        abort_unless($resume->hasStoredFile(), 404);

        $disk = Storage::disk('local');

        return response()->streamDownload(function () use ($disk, $resume): void {
            $stream = $disk->readStream($resume->file_path);

            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, $resume->downloadName(), [
            'Content-Type' => $resume->mime_type ?: 'application/octet-stream',
        ]);
    })->name('resumes.download');
});

require __DIR__.'/settings.php';
