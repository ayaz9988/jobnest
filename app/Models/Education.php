<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    protected $table = 'educations';

    protected $fillable = [
        'user_id',
        'degree',
        'institution',
        'field_of_study',
        'started_at',
        'ended_at',
        'is_current',
        'description',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function period(): string
    {
        $start = $this->started_at->format('Y-m');

        $end = $this->is_current
            ? 'حتى الآن'
            : ($this->ended_at?->format('Y-m') ?? '');

        return trim($start.' - '.$end, ' -');
    }
}
