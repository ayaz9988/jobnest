<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    //
    protected $fillable = ['name', 'slug', 'parent_id'];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany<JobListing, $this>
     */
    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    /**
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        if (! $this->exists) {
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
                ->map(fn ($id): int => (int) $id)
                ->all();

            $ids = [...$ids, ...$found];
            $frontier = $found;
        }

        return $ids;
    }
}
