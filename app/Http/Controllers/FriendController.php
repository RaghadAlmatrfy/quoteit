<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Notifications\InteractionNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    /**
     * Friends list + incoming and sent requests.
     */
    public function index(): View
    {
        $me = Auth::user();

        return view('friends.index', [
            'friends' => $me->friends(),
            'incoming' => $me->receivedFriendRequests()->where('status', Friendship::Pending)->with('requester')->latest()->get(),
            'outgoing' => $me->sentFriendRequests()->where('status', Friendship::Pending)->with('addressee')->latest()->get(),
            'page' => 'Friends',
        ]);
    }

    /**
     * Add a friend. Public accounts become friends straight away;
     * private accounts get a request they have to accept.
     * (If they already sent me a request, this accepts it.)
     */
    public function store(User $user): RedirectResponse
    {
        $me = Auth::user();
        abort_if($me->is($user), 422);

        $existing = $me->friendshipWith($user);

        if ($existing === null && ! $user->is_private) {
            Friendship::create(['requester_id' => $me->id, 'addressee_id' => $user->id, 'status' => Friendship::Accepted]);
            InteractionNotification::send($user, InteractionNotification::FriendAdded, $me);

            return redirect()->back()->with('status', __('You are now friends.'));
        }

        if ($existing === null) {
            Friendship::create(['requester_id' => $me->id, 'addressee_id' => $user->id]);
            InteractionNotification::send($user, InteractionNotification::FriendRequest, $me);

            return redirect()->back()->with('status', __('Friend request sent.'));
        }

        if ($existing->status === Friendship::Pending && $existing->addressee_id === $me->id) {
            $existing->update(['status' => Friendship::Accepted]);
            InteractionNotification::send($user, InteractionNotification::FriendAccepted, $me);

            return redirect()->back()->with('status', __('You are now friends.'));
        }

        return redirect()->back();
    }

    /**
     * Accept a request that was sent to me.
     */
    public function update(Friendship $friendship): RedirectResponse
    {
        abort_unless($friendship->addressee_id === Auth::id(), 403);

        $friendship->update(['status' => Friendship::Accepted]);
        InteractionNotification::send($friendship->requester, InteractionNotification::FriendAccepted, Auth::user());

        return redirect()->back()->with('status', __('You are now friends.'));
    }

    /**
     * Decline / cancel a request, or remove a friend.
     */
    public function destroy(Friendship $friendship): RedirectResponse
    {
        abort_unless(in_array(Auth::id(), [$friendship->requester_id, $friendship->addressee_id], true), 403);

        $friendship->delete();

        return redirect()->back()->with('status', __('Done.'));
    }
}
