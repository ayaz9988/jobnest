<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobListing extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'posted_by',
        'category_id',
        'title',
        'description',
        'employment_type',
        'workplace_type',
        'location',
        'salary_min',
        'salary_max',
        'salary_currency',
        'status',
        'expires_at',
        'published_at'
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_listing_skill', 'job_listing_id', 'skill_id')
        ->withPivot('is_required')
        ->withTimestamps();
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_job_listings')
        ->withTimestamps();
    }
}
