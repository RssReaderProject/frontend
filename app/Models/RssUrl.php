<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class RssUrl extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'user_id'];

    /**
     * Get the user that owns the RSS URL.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all RSS URLs for a user, ordered latest first.
     */
    public static function forUser(User $user): Collection
    {
        return static::where('user_id', $user->id)->latest()->get();
    }

    /**
     * Find a specific RSS URL by id for a user.
     */
    public static function findByUser(User $user, $id): ?self
    {
        return static::where('user_id', $user->id)->where('id', $id)->first();
    }
}
