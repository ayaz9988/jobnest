<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
