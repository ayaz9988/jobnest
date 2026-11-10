<?php

namespace App\Models;

use Database\Factories\ResumeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resume extends Model
{
    /** @use HasFactory<ResumeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'file_url',
        'file_name',
        'mime_type',
        'user_id',
        'file_size',
        'is_primary',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
    ];

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

    public function downloadUrl(): string
    {
        return route('resumes.download', $this);
    }
}
