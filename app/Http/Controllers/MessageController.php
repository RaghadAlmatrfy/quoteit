<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Notifications\InteractionNotification;
use App\Support\MediaUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Conversations: one row per friend, with the last message.
     */
    public function index(): View
    {
        $me = Auth::user();

        $conversations = $me->friends()->map(fn (User $friend) => [
            'friend' => $friend,
            'last' => Message::conversation($me, $friend)->latest('id')->first(),
            'unread' => Message::where('sender_id', $friend->id)->where('receiver_id', $me->id)->whereNull('read_at')->count(),
        ])->sortByDesc(fn (array $row) => $row['last']?->id ?? 0)->values();

        return view('messages.index', ['conversations' => $conversations, 'page' => 'Messages']);
    }

    /**
     * The private chat with one friend.
     */
    public function show(User $user): View
    {
        $me = Auth::user();
        abort_unless($me->isFriendsWith($user), 403);

        Message::where('sender_id', $user->id)->where('receiver_id', $me->id)->whereNull('read_at')->update(['read_at' => now()]);

        return view('messages.show', [
            'friend' => $user,
            'messages' => Message::conversation($me, $user)->oldest('id')->get(),
            'page' => 'Messages',
        ]);
    }

    public function store(MessageRequest $request, User $user): RedirectResponse
    {
        $data = [
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'body' => $request->validated('body'),
        ];

        if ($request->hasFile('media')) {
            $data += MediaUpload::store($request->file('media'), 'messages');
        }

        Message::create($data);
        InteractionNotification::send($user, InteractionNotification::Message, Auth::user());

        return redirect()->route('messages.show', $user);
    }
}
