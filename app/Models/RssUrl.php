<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url', 
        'user_id', 
        'consecutive_failures', 
        'last_failure_at', 
        'disabled_at'
    ];

    protected $casts = [
        'last_failure_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /**
     * Get the user that owns the RSS URL.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the RSS items that belong to this URL.
     */
    public function rssItems(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }

    /**
     * Check if the RSS URL is disabled.
     */
    public function getIsDisabledAttribute(): bool
    {
        return $this->disabled_at !== null;
    }

    /**
     * Check if the RSS URL should be skipped due to recent failures.
     * This must match the logic in scopeActive.
     */
    public function shouldSkipFetch(): bool
    {
        // If permanently disabled, always skip
        if ($this->is_disabled) {
            return true;
        }

        // If less than 3 failures, always fetch
        if ($this->consecutive_failures < 3) {
            return false;
        }

        // If last_failure_at is null, always fetch
        if ($this->last_failure_at === null) {
            return false;
        }

        // If 3 or more failures, but less than 10, and last failure was more than 1 day ago, fetch
        if ($this->consecutive_failures < 10 && $this->last_failure_at->lt(now()->subDay())) {
            return false;
        }

        // Otherwise, skip
        return true;
    }

    /**
     * Record a successful fetch (reset failure count).
     */
    public function recordSuccess(): void
    {
        $this->update([
            'consecutive_failures' => 0,
            'last_failure_at' => null,
        ]);
    }

    /**
     * Record a failed fetch.
     */
    public function recordFailure(): void
    {
        $newFailureCount = $this->consecutive_failures + 1;
        
        $updateData = [
            'consecutive_failures' => $newFailureCount,
            'last_failure_at' => now(),
        ];

        // If we've failed 10 times, permanently disable
        if ($newFailureCount >= 10) {
            $updateData['disabled_at'] = now();
        }

        $this->update($updateData);
    }

    /**
     * Re-enable a disabled RSS URL.
     */
    public function reEnable(): void
    {
        $this->update([
            'consecutive_failures' => 0,
            'last_failure_at' => null,
            'disabled_at' => null,
        ]);
    }

    /**
     * Scope to get only active RSS URLs (not disabled and not in cooldown).
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('disabled_at')
              ->where(function ($subQ) {
                  // Either less than 3 failures, or last failure was more than 1 day ago, or less than 10 failures
                  $subQ->where('consecutive_failures', '<', 3)
                       ->orWhereNull('last_failure_at')
                       ->orWhere(function ($q2) {
                           $q2->where('consecutive_failures', '>=', 3)
                              ->where('consecutive_failures', '<', 10)
                              ->where('last_failure_at', '<', now()->subDay());
                       });
              });
        });
    }

    /**
     * Get all RSS URLs for a user, ordered latest first.
     */
    public static function forUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return static::where('user_id', $user->id)->latest()->get();
    }

    /**
     * Get all active RSS URLs for a user, ordered latest first.
     */
    public static function activeForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return static::where('user_id', $user->id)->active()->latest()->get();
    }

    /**
     * Find a specific RSS URL by id for a user.
     */
    public static function findByUser(User $user, $id): ?self
    {
        return static::where('user_id', $user->id)->where('id', $id)->first();
    }
}
