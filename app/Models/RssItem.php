<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RssItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'source',
        'source_url',
        'link',
        'publish_date',
        'description',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
    ];

    /**
     * Get the user that owns the RSS item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all RSS items for a user, ordered by publish date (latest first).
     */
    public static function forUser($user): Collection
    {
        if (! $user) {
            return collect();
        }

        return static::where('user_id', $user->id)
            ->orderBy('publish_date', 'desc')
            ->get();
    }

    /**
     * Find a specific RSS item by id for a user.
     */
    public static function findByUser(User $user, $id): ?self
    {
        return static::where('user_id', $user->id)->where('id', $id)->first();
    }

    /**
     * Get RSS items for a user with pagination, ordered by publish date (latest first).
     */
    public static function forUserPaginated($user, $perPage = 20)
    {
        if (! $user) {
            return collect();
        }

        return static::where('user_id', $user->id)
            ->orderBy('publish_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent RSS items for a user (last 7 days).
     */
    public static function recentForUser($user, $days = 7): Collection
    {
        if (! $user) {
            return collect();
        }

        return static::where('user_id', $user->id)
            ->where('publish_date', '>=', now()->subDays($days))
            ->orderBy('publish_date', 'desc')
            ->get();
    }
}
