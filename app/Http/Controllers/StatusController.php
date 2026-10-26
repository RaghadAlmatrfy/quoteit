<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\StatusView;
use App\Models\User;
use App\Notifications\InteractionNotification;
use App\Support\MediaUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    /**
     * Add a status (text on a color, or an image / video). It lives for 24 hours.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'body' => ['nullable', 'required_without:media', 'max:200'],
            'bg_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'media' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov', 'max:20480'],
            'visibility' => ['nullable', 'in:public,friends'],
        ]);

        $data = [
            'body' => $request->input('body'),
            'bg_color' => $request->input('bg_color') ?: '#432a80',
            'visibility' => $request->input('visibility', Status::Public),
            'expires_at' => now()->addHours(Status::HoursAlive),
        ];

        if ($request->hasFile('media')) {
            $data += MediaUpload::store($request->file('media'), 'statuses');
        }

        Auth::user()->statuses()->create($data);

        return redirect()->route('home')->with('status', __('Your status is live for 24 hours.'));
    }

    /**
     * Remember that I saw this status (so its owner knows who saw it).
     */
    public function view(Status $status): JsonResponse
    {
        abort_unless($status->isVisibleTo(Auth::user()), 403);

        if ($status->user_id !== Auth::id()) {
            StatusView::firstOrCreate(['status_id' => $status->id, 'user_id' => Auth::id()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Like / unlike someone's status.
     */
    public function like(Status $status): JsonResponse
    {
        abort_unless($status->isVisibleTo(Auth::user()), 403);
        abort_if($status->user_id === Auth::id(), 422);

        $view = StatusView::firstOrCreate(['status_id' => $status->id, 'user_id' => Auth::id()]);
        $view->update(['liked' => ! $view->liked]);

        if ($view->liked) {
            InteractionNotification::send($status->user, InteractionNotification::StatusLiked, Auth::user());
        }

        return response()->json(['liked' => $view->liked]);
    }

    public function destroy(Status $status): RedirectResponse
    {
        abort_if($status->user_id !== Auth::id(), 403);

        MediaUpload::delete($status->media_path);
        $status->delete();

        return redirect()->route('home')->with('status', __('Your status has been deleted.'));
    }

    /**
     * Active statuses grouped by person (me first), ready for the viewer.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function groupsFor(?User $viewer): array
    {
        return Status::active()
            ->visibleTo($viewer)
            ->with(['user', 'views.user'])
            ->oldest()
            ->get()
            ->groupBy('user_id')
            ->sortByDesc(fn ($statuses) => $viewer && $statuses->first()->user_id === $viewer->id ? PHP_INT_MAX : $statuses->last()->id)
            ->map(fn ($statuses) => [
                'user' => [
                    'name' => $statuses->first()->user->name,
                    'username' => $statuses->first()->user->username,
                    'avatar' => $statuses->first()->user->avatarUrl(),
                    'mine' => $viewer && $statuses->first()->user_id === $viewer->id,
                    'url' => route('users.show', $statuses->first()->user),
                ],
                'items' => $statuses->map(fn (Status $status) => [
                    'id' => $status->id,
                    'body' => $status->body,
                    'bg' => $status->bg_color,
                    'media' => $status->mediaUrl(),
                    'type' => $status->media_type,
                    'ago' => $status->created_at->diffForHumans(),
                    'friendsOnly' => $status->visibility === Status::FriendsOnly,
                    'viewUrl' => $viewer ? route('statuses.view', $status) : null,
                    'likeUrl' => $viewer && $status->user_id !== $viewer->id ? route('statuses.like', $status) : null,
                    'liked' => $viewer ? (bool) $status->views->firstWhere('user_id', $viewer->id)?->liked : false,
                    'deleteUrl' => $viewer && $status->user_id === $viewer->id ? route('statuses.destroy', $status) : null,
                    // Only the owner sees who viewed and who liked
                    'viewers' => $viewer && $status->user_id === $viewer->id
                        ? $status->views->map(fn (StatusView $view) => [
                            'name' => $view->user->name,
                            'avatar' => $view->user->avatarUrl(),
                            'url' => route('users.show', $view->user),
                            'liked' => $view->liked,
                        ])->values()->all()
                        : null,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }
}
