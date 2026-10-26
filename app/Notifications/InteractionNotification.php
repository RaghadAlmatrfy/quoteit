<?php

namespace App\Notifications;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * "Someone interacted with you": liked or saved your quote,
 * sent / accepted a friend request, or sent you a message.
 */
class InteractionNotification extends Notification
{
    public const Liked = 'liked';

    public const Saved = 'saved';

    public const FriendRequest = 'friend_request';

    public const FriendAccepted = 'friend_accepted';

    public const Message = 'message';

    public const StatusLiked = 'status_liked';

    public function __construct(
        public string $kind,
        public User $actor,
        public ?Quote $quote = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_username' => $this->actor->username,
            'quote_id' => $this->quote?->id,
            'excerpt' => $this->quote ? mb_strimwidth($this->quote->content, 0, 60, '…') : null,
        ];
    }

    /** Send it, unless you are interacting with yourself. */
    public static function send(User $to, string $kind, User $actor, ?Quote $quote = null): void
    {
        if (! $to->is($actor)) {
            $to->notify(new self($kind, $actor, $quote));
        }
    }
}
