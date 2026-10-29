<?php

namespace App\Models;

use App\Support\QuoteStyle;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'body', 'font'])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The CSS font of the comment, or null for the site font. */
    public function fontFamily(): ?string
    {
        return QuoteStyle::family($this->font);
    }

    /** The comment's author or the quote's owner can delete it. */
    public function canBeDeletedBy(?User $user): bool
    {
        return $user !== null && ($user->id === $this->user_id || $user->id === $this->quote->user_id);
    }
}
