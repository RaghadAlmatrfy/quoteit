<?php

namespace App\Models;

use App\Support\MediaUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sender_id', 'receiver_id', 'body', 'media_path', 'media_type', 'read_at'])]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /** All messages between two users, in both directions. */
    public function scopeConversation(Builder $query, User $first, User $second): void
    {
        $query->where(function (Builder $query) use ($first, $second) {
            $query->where(fn (Builder $q) => $q->where('sender_id', $first->id)->where('receiver_id', $second->id))
                ->orWhere(fn (Builder $q) => $q->where('sender_id', $second->id)->where('receiver_id', $first->id));
        });
    }

    public function mediaUrl(): ?string
    {
        return MediaUpload::url($this->media_path);
    }
}
