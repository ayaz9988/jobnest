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

    Route::get('resumes/{resume}/download', function (Resume $resume) {
        abort_unless($resume->user_id === Auth::id(), 403);

        return Storage::disk('local')->download($resume->file_url, $resume->file_name);
    })->name('resumes.download');
});

require __DIR__.'/settings.php';
