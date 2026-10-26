<?php

namespace App\Support;

use App\Enums\Field;
use App\Models\Quote;
use App\Models\Reaction;
use App\Models\User;

/**
 * "My interests": what I like, save, copy and share, and in which fields.
 * Fields I never interacted with are not shown at all.
 */
class Interests
{
    /**
     * @return array{likes: int, saves: int, shares: int, total: int, fields: array<int, array{field: Field, count: int, percent: int}>}
     */
    public static function for(User $user): array
    {
        $likedFields = Quote::whereIn('id', Reaction::where('user_id', $user->id)->where('type', Reaction::Like)->select('quote_id'))->pluck('field');
        $savedFields = $user->favorites()->pluck('field');
        $sharedFields = Quote::join('quote_shares', 'quote_shares.quote_id', '=', 'quotes.id')
            ->where('quote_shares.user_id', $user->id)
            ->pluck('quotes.field');

        $all = $likedFields->concat($savedFields)->concat($sharedFields)
            ->map(fn ($field) => $field instanceof Field ? $field : Field::from($field));
        $total = $all->count();

        $fields = $all->countBy(fn (Field $field) => $field->value)
            ->sortDesc()
            ->map(fn (int $count, string $value) => [
                'field' => Field::from($value),
                'count' => $count,
                'percent' => (int) round($count / max($total, 1) * 100),
            ])
            ->values()
            ->all();

        return [
            'likes' => $likedFields->count(),
            'saves' => $savedFields->count(),
            'shares' => $sharedFields->count(),
            'total' => $total,
            'fields' => $fields,
        ];
    }
}
