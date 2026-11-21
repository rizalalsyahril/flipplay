<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected $fillable = [
        'anilist_id',
        'title',
        'slug',
        'type',
        'cover_url',
        'trailer_url',
        'synopsis',
        'episodes',
        'year',
        'score',
        'genres',
    ];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'score' => 'float',
            'year' => 'integer',
            'episodes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Media $media): void {
            if (! $media->slug) {
                $media->slug = Str::slug($media->title);
            }
        });
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['status', 'progress'])
            ->withTimestamps();
    }
}
