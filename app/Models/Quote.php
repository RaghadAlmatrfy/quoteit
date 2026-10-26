<?php

namespace App\Models;

use App\Enums\Field;
use App\Support\MediaUpload;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['content', 'field', 'media_path', 'media_type', 'status', 'published_at', 'font', 'font_size', 'is_bold', 'is_italic', 'text_align', 'bg_color'])]
class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    public const Draft = 'draft';

    public const Published = 'published';

    protected function casts(): array
    {
        return [
            'field' => Field::class,
            'published_at' => 'datetime',
            'is_bold' => 'boolean',
            'is_italic' => 'boolean',
        ];
    }

    /** Only quotes that are published and whose time has come (not drafts, not scheduled). */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', self::Published)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isDraft(): bool
    {
        return $this->status === self::Draft;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::Published && $this->published_at?->isFuture();
    }

    public function isLive(): bool
    {
        return ! $this->isDraft() && ! $this->isScheduled();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Only the published quotes $viewer is allowed to see:
     * quotes of public accounts + their own quotes + quotes of their friends.
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        $query->published()->where(function (Builder $query) use ($viewer) {
            $query->whereHas('user', fn (Builder $user) => $user->where('is_private', false));

            if ($viewer) {
                $query->orWhereIn('user_id', [$viewer->id, ...$viewer->friendIds()]);
            }
        });
    }

    /** Eager-load everything a quote card needs (avoids N+1 queries). */
    public function scopeForCards(Builder $query, ?User $viewer): void
    {
        $query->with('user')
            ->withCount([
                'reactions as likes_count' => fn (Builder $reaction) => $reaction->where('type', Reaction::Like),
                'reactions as dislikes_count' => fn (Builder $reaction) => $reaction->where('type', Reaction::Dislike),
            ]);

        if ($viewer) {
            $query->with([
                'reactions' => fn ($reaction) => $reaction->where('user_id', $viewer->id),
                'favoritedBy' => fn ($user) => $user->where('users.id', $viewer->id),
            ]);
        }
    }

    public function isVisibleTo(?User $viewer): bool
    {
        if ($viewer?->id === $this->user_id) {
            return true;
        }

        return $this->isLive() && $this->user->isVisibleTo($viewer);
    }

    public function mediaUrl(): ?string
    {
        return MediaUpload::url($this->media_path);
    }
}
