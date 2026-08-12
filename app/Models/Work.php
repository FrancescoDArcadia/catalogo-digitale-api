<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'publication_year',
        'author_id',
        'category_id',
    ];

    protected $casts = [
        'publication_year' => 'integer',
    ];

    public function author(): BelongsTo {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $value) => $q->where('title', 'like', "%{$value}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $value) => $q->where('category_id', $value))
            ->when($filters['author_id'] ?? null, fn ($q, $value) => $q->where('author_id', $value))
            ->when($filters['tag_id'] ?? null, function ($q, $value) {
                $q->whereHas('tags', fn ($tagQuery) => $tagQuery->where('tag.id', $value));
            })
            ->when($filters['year_from'] ?? null, fn ($q, $value) => $q->where('publication_year', '>=', $value))
            ->when($filters['year_to'] ?? null, fn ($q, $value) => $q->where('publication_year', '<=', $value));
    }

    public function scopeSort(Builder $query, ?string $sortBy, ?string $sortDir='asc'): Builder
    {
        $allowed = ['title', 'publication_year', 'created_at'];
        if (!in_array($sortBy, $allowed, true)) {
            $sortBy = 'created_at';
        }

        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';
        return $query->orderBy($sortBy, $sortDir);
    }
}
