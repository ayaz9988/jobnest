<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    protected $fillable = ['name'];

    public function jobListings()
    {
        return $this->belongsToMany(JobListing::class, 'job_listing_skill', 'skill_id', 'job_listing_id')
        ->withPivot('is_required')
        ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skill', 'skill_id', 'user_id')
        ->withPivot(['proficiency', 'years_of_experience'])
        ->withTimestamps();
    }
}
