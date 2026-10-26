<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Quote;
use App\Notifications\InteractionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class CommentController extends Controller
{
    /**
     * The comments of a quote (loaded when you open them).
     */
    public function index(Quote $quote): Response
    {
        abort_unless($quote->isVisibleTo(Auth::user()), 403);

        return response($this->render($quote));
    }

    /**
     * Add a comment.
     */
    public function store(Request $request, Quote $quote): Response|RedirectResponse
    {
        abort_unless($quote->isVisibleTo(Auth::user()) && $quote->isLive(), 403);

        $data = $request->validate(['body' => ['required', 'string', 'min:1', 'max:300']]);

        $quote->comments()->create(['user_id' => Auth::id(), 'body' => $data['body']]);
        InteractionNotification::send($quote->user, InteractionNotification::Commented, Auth::user(), $quote);

        return $request->expectsJson() || $request->ajax()
            ? response($this->render($quote))
            : redirect()->back();
    }

    /**
     * Delete a comment (its author or the quote's owner).
     */
    public function destroy(Request $request, Comment $comment): Response|RedirectResponse
    {
        abort_unless($comment->canBeDeletedBy(Auth::user()), 403);

        $quote = $comment->quote;
        $comment->delete();

        return $request->expectsJson() || $request->ajax()
            ? response($this->render($quote))
            : redirect()->back();
    }

    private function render(Quote $quote): string
    {
        return Blade::render('<x-comments-list :quote="$quote" :comments="$comments" />', [
            'quote' => $quote,
            'comments' => $quote->comments()->with(['user', 'quote'])->oldest()->get(),
        ]);
    }
}
