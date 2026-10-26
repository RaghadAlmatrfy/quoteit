<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['requester_id', 'addressee_id', 'status'])]
class Friendship extends Model
{
    public const Pending = 'pending';

    public const Accepted = 'accepted';

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    /** The friendship between two users, whoever sent the request. */
    public function scopeBetween(Builder $query, User $first, User $second): void
    {
        $query->where(function (Builder $query) use ($first, $second) {
            $query->where(fn (Builder $q) => $q->where('requester_id', $first->id)->where('addressee_id', $second->id))
                ->orWhere(fn (Builder $q) => $q->where('requester_id', $second->id)->where('addressee_id', $first->id));
        });
    }
}
