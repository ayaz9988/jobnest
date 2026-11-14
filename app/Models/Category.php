<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $fillable = ['name', 'slug', 'parent_id'];
    
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }

    public function descendantIds(): array
    {
        if(! $this->exists)
        {
            return [];
        }

        $ids = [];
        $frontier = [$this->getKey()];

        while ($frontier !== []) {
            $found = static::query()
                ->whereIn('parent_id', $frontier)
                ->whereNotIn('id', $ids)
                ->whereKeyNot($this->getKey())
                ->pluck('id')
                ->all();

            $ids = [...$ids, ...$found];
            $frontier = $found;
        }

        return $ids;
    }
}
