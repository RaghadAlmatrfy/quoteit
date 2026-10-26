<?php

namespace App\Http\Controllers;

use App\Enums\Field;
use App\Http\Requests\QuoteRequest;
use App\Models\Quote;
use App\Support\Interests;
use App\Support\MediaUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class QuoteController extends Controller
{
    /**
     * Home page: the "All fields" community feed.
     */
    public function index(Request $request): View
    {
        $viewer = Auth::user();
        $activeField = Field::tryFrom((string) $request->query('field'));

        $quotes = Quote::visibleTo($viewer)
            ->forCards($viewer)
            ->when($activeField, fn ($query) => $query->where('field', $activeField))
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('welcome', [
            'quotes' => $quotes,
            'activeField' => $activeField,
            'composerField' => $activeField,
            'interests' => $viewer ? Interests::for($viewer) : null,
            'statusGroups' => StatusController::groupsFor($viewer),
            'page' => 'Home',
        ]);
    }

    /**
     * One quote on its own page (used by "copy link" and "share").
     */
    public function show(Quote $quote): View
    {
        abort_unless($quote->isVisibleTo(Auth::user()), 403);

        $quote = Quote::forCards(Auth::user())->findOrFail($quote->id);

        return view('quotes.show', ['quote' => $quote, 'page' => 'Quote']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QuoteRequest $request): RedirectResponse
    {
        $data = [...$request->safe()->only(['content', 'field']), ...$request->styleAttributes()];

        $data += match ($request->input('intent', 'publish')) {
            'draft' => ['status' => Quote::Draft, 'published_at' => null],
            'schedule' => ['status' => Quote::Published, 'published_at' => Carbon::parse($request->input('publish_at'))->utc()],
            default => ['status' => Quote::Published, 'published_at' => now()],
        };

        if ($request->hasFile('media')) {
            $data += MediaUpload::store($request->file('media'), 'quotes');
        }

        $quote = Auth::user()->quotes()->create($data);

        $message = match (true) {
            $quote->isDraft() => __('Saved as a draft.'),
            $quote->isScheduled() => __('Your quote is scheduled.'),
            default => __('Your quote has been shared.'),
        };

        return $quote->isLive()
            ? redirect()->back()->with('status', $message)
            : redirect()->route('drafts.index')->with('status', $message);
    }

    /**
     * My drafts and scheduled quotes.
     */
    public function drafts(): View
    {
        $quotes = Quote::forCards(Auth::user())
            ->where('user_id', Auth::id())
            ->where(fn ($query) => $query->where('status', Quote::Draft)->orWhere('published_at', '>', now()))
            ->latest('updated_at')
            ->paginate(15);

        return view('drafts', ['quotes' => $quotes, 'page' => 'Drafts']);
    }

    /**
     * Publish a draft or a scheduled quote right now.
     */
    public function publish(Quote $quote): RedirectResponse
    {
        abort_if($quote->user_id != Auth::id(), 403);

        $quote->update(['status' => Quote::Published, 'published_at' => now()]);

        return redirect()->route('home')->with('status', __('Your quote has been shared.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(QuoteRequest $request, Quote $quote): RedirectResponse
    {
        abort_if($quote->user_id != Auth::id(), 403);

        $data = [...$request->safe()->only(['content', 'field']), ...$request->styleAttributes()];

        if ($request->boolean('remove_media') || $request->hasFile('media')) {
            MediaUpload::delete($quote->media_path);
            $data += ['media_path' => null, 'media_type' => null];
        }

        if ($request->hasFile('media')) {
            $data = [...$data, ...MediaUpload::store($request->file('media'), 'quotes')];
        }

        $quote->update($data);

        return redirect()->back()->with('status', __('Your quote has been updated.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        abort_if($quote->user_id != Auth::id(), 403);

        MediaUpload::delete($quote->media_path);
        $quote->delete();

        return redirect()->back()->with('status', __('Your quote has been deleted.'));
    }
}
