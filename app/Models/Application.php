<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    public const STATUS_APPLIED = 'applied';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_WITHDRAWN = 'withdraw';

    public const STATUS_HIRED = 'hired';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Application states the seeker is still allowed to withdraw from.
     *
     * @var list<string>
     */
    public const WITHDRAWABLE_STATUSES = [
        self::STATUS_APPLIED,
        self::STATUS_REVIEWED,
        self::STATUS_INTERVIEW,
    ];

    //

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'cover_letter',
        'status',
        'resume_id',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<JobListing, $this>
     */
    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    /**
     * @return BelongsTo<Resume, $this>
     */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function isWithdrawn(): bool
    {
        return $this->status === self::STATUS_WITHDRAWN;
    }

    public function canBeWithdrawn(): bool
    {
        return in_array($this->status, self::WITHDRAWABLE_STATUSES, true);
    }
}
