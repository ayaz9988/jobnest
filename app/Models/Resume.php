<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'mime_type',
        'user_id',
        'file_size',
        'file_path',
        'is_primary',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Resume $resume): void {
            if (blank($resume->file_path)) {
                return;
            }

            $disk = Storage::disk('local');

            if ($disk->exists($resume->file_path)) {
                $resume->file_size = $disk->size($resume->file_path);

                $resolvedPath = $disk->path($resume->file_path);
                $resume->mime_type = mime_content_type($resolvedPath) ?: $resume->mime_type;
            }

            if (blank($resume->file_name)) {
                $resume->file_name = basename($resume->file_path);
            }
        });

        static::updated(function (Resume $resume): void {
            if (! $resume->wasChanged('file_path')) {
                return;
            }

            $oldPath = $resume->getOriginal('file_path');

            if ($oldPath && $oldPath !== $resume->file_path) {
                Storage::disk('local')->delete($oldPath);
            }
        });

        static::deleted(function (Resume $resume): void {
            if (filled($resume->file_path)) {
                Storage::disk('local')->delete($resume->file_path);
            }
        });
    }

    public function downloadName(): string
    {
        return basename($this->file_name ?: $this->file_path ?: 'resume');
    }

    public function downloadUrl(): string
    {
        return route('resumes.download', $this);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

}
