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

        $communities = collect(Field::cases())->map(fn (Field $field) => [
            'field' => $field,
            'quotes' => Quote::visibleTo($viewer)->where('field', $field)->count(),
            // everyone is a member of "General Tech"
            'members' => $field === Field::General
                ? User::count()
                : User::where('specialty', $field)->orWhereJsonContains('interests', $field->value)->count(),
        ]);

        return view('communities.index', [
            'communities' => $communities,
            'allQuotes' => Quote::visibleTo($viewer)->count(),
            'allMembers' => User::count(),
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
