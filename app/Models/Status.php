<?php

namespace App\Models;

use App\Support\MediaUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A temporary status: each one is shown for 10 seconds and disappears after 24 hours.
 */
#[Fillable(['body', 'bg_color', 'media_path', 'media_type', 'visibility', 'expires_at'])]
class Status extends Model
{
    use Prunable;

    public const SecondsOnScreen = 10;

    public const HoursAlive = 24;

    public const Public = 'public';

    public const FriendsOnly = 'friends';

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StatusView::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('expires_at', '>', now());
    }

    /**
     * Who can see a status:
     * "public" statuses of public accounts -> everyone; my own; and my friends' (public or friends-only).
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        $query->where(function (Builder $query) use ($viewer) {
            $query->where('visibility', self::Public)
                ->whereHas('user', fn (Builder $user) => $user->where('is_private', false));

            if ($viewer) {
                $query->orWhereIn('user_id', [$viewer->id, ...$viewer->friendIds()]);
            }
        });
    }

    /** "php artisan model:prune" deletes expired statuses (and their files). */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now());
    }

    protected function pruning(): void
    {
        MediaUpload::delete($this->media_path);
    }

    public function isVisibleTo(?User $viewer): bool
    {
        return static::whereKey($this->id)->active()->visibleTo($viewer)->exists();
    }

    public function mediaUrl(): ?string
    {
        return MediaUpload::url($this->media_path);
    }
}
