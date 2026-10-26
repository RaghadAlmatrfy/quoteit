<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'quote_id', 'type'])]
class QuoteShare extends Model
{
    public const Copy = 'copy';

    public const Share = 'share';

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
