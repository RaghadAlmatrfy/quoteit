<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * The latest notifications of the logged-in user, loaded once per page
 * (the bell and the notifications panel both use them).
 */
class NotificationFeed
{
    /**
     * @return array{notifications: Collection<int, DatabaseNotification>, unreadCount: int, actors: Collection<int, User>}
     */
    public static function for(User $user): array
    {
        $key = 'notification-feed-'.$user->id;
        $attributes = request()->attributes;

        if (! $attributes->has($key)) {
            $notifications = $user->notifications()->latest()->limit(15)->get();

            $attributes->set($key, [
                'notifications' => $notifications,
                'unreadCount' => $user->unreadNotifications()->count(),
                'actors' => User::whereIn('id', $notifications->pluck('data.actor_id')->filter()->unique())->get()->keyBy('id'),
            ]);
        }

        return $attributes->get($key);
    }
}
