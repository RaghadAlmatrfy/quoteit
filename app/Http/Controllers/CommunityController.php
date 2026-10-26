<?php

namespace App\Http\Controllers;

use App\Enums\Field;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    /**
     * All communities: one per field + one for every field.
     */
    public function index(): View
    {
        $viewer = Auth::user();

        // Two small queries for all the numbers (fast even with a far-away database)
        $quotesPerField = Quote::visibleTo($viewer)->toBase()->selectRaw('field, count(*) as total')->groupBy('field')->pluck('total', 'field');
        $people = User::query()->get(['specialty', 'interests']);

        $communities = collect(Field::cases())->map(fn (Field $field) => [
            'field' => $field,
            'quotes' => (int) ($quotesPerField[$field->value] ?? 0),
            // everyone is a member of "General Tech"
            'members' => $field === Field::General
                ? $people->count()
                : $people->filter(fn (User $user) => $user->specialty === $field || in_array($field->value, $user->interests ?? [], true))->count(),
        ]);

        return view('communities.index', [
            'communities' => $communities,
            'allQuotes' => (int) $quotesPerField->sum(),
            'allMembers' => $people->count(),
            'page' => 'Communities',
        ]);
    }

    /**
     * One community: its feed and its members.
     * /communities/all shows every field together.
     */
    public function show(string $community): View
    {
        $viewer = Auth::user();
        $field = $community === 'all' ? null : Field::tryFrom($community);
        abort_if($community !== 'all' && $field === null, 404);

        $quotes = Quote::visibleTo($viewer)
            ->forCards($viewer)
            ->when($field, fn ($query) => $query->where('field', $field))
            ->latest()
            ->latest('id')
            ->paginate(15);

        $members = User::query()
            ->when($field && $field !== Field::General, fn ($query) => $query->where('specialty', $field)->orWhereJsonContains('interests', $field->value))
            ->orderBy('name')
            ->limit(24)
            ->get();

        return view('communities.show', [
            'field' => $field,
            'composerField' => $field,
            'quotes' => $quotes,
            'members' => $members,
            'page' => 'Communities',
        ]);
    }
}
