<?php

namespace App\Models;

use Database\Factories\ResumeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Resume extends Model
{
    /** @use HasFactory<ResumeFactory> */
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

            if (self::isStoredFilePath($resume->file_path)) {
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

            if ($oldPath && $oldPath !== $resume->file_path && self::isStoredFilePath($oldPath)) {
                Storage::disk('local')->delete($oldPath);
            }
        });

        static::deleted(function (Resume $resume): void {
            if (self::isStoredFilePath($resume->file_path)) {
                Storage::disk('local')->delete($resume->file_path);
            }
        });
    }

    public function downloadName(): string
    {
        return basename($this->file_name ?: $this->file_path ?: 'resume');
    }

    public function hasStoredFile(): bool
    {
        return self::isStoredFilePath($this->file_path);
    }

    private static function isStoredFilePath(?string $path): bool
    {
        if (blank($path)) {
            return false;
        }

        $disk = Storage::disk('local');

        try {
            $root = realpath($disk->path('resumes'));
            $fullPath = realpath($disk->path($path));
        } catch (Throwable) {
            return false;
        }

        return $root !== false
            && $fullPath !== false
            && str_starts_with($fullPath, $root.DIRECTORY_SEPARATOR);
    }

    public function downloadUrl(): string
    {
        return route('resumes.download', ['resume' => $this]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
