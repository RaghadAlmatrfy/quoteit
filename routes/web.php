<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SignupController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TranslationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Home: the "All fields" community feed
Route::get('/', [QuoteController::class, 'index'])->name('home');

// Language: /locale/ar or /locale/en
Route::get('/locale/{locale}', [LocaleController::class, 'update'])->name('locale');

// Authentication (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/signup', function () {
        return view('Authentication.SignUp', ['page' => 'Sign Up']);
    })->name('signup');
    Route::post('/signup', [SignupController::class, 'signup']);

    Route::get('/login', function () {
        return view('Authentication.LogIn', ['page' => 'Log In']);
    })->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Translate a quote inside the site (max 30 per minute per visitor)
Route::get('/translate', [TranslationController::class, 'show'])->middleware('throttle:30,1')->name('translate');

// Public pages (privacy rules are checked inside the controllers)
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::get('/quotes/{quote}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/communities/{community}', [CommunityController::class, 'show'])->name('communities.show');

// Logged-in users only
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Quotes: POST /quotes, PUT /quotes/{quote}, DELETE /quotes/{quote}
    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    // Drafts and scheduled quotes
    Route::get('/drafts', [QuoteController::class, 'drafts'])->name('drafts.index');
    Route::post('/quotes/{quote}/publish', [QuoteController::class, 'publish'])->name('quotes.publish');

    // Like / dislike and favorites
    Route::post('/quotes/{quote}/reaction', [ReactionController::class, 'store'])->name('quotes.react');
    Route::post('/quotes/{quote}/favorite', [FavoriteController::class, 'toggle'])->name('quotes.favorite');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // Comments
    Route::post('/quotes/{quote}/comments', [CommentController::class, 'store'])->middleware('throttle:20,1')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // My profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Friends
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/users/{user}/friend', [FriendController::class, 'store'])->name('friends.store');
    Route::patch('/friendships/{friendship}', [FriendController::class, 'update'])->name('friends.accept');
    Route::delete('/friendships/{friendship}', [FriendController::class, 'destroy'])->name('friends.destroy');

    // Private messages (friends only)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');

    // Notifications (people who interacted with me)
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');

    // Temporary statuses (24 hours)
    Route::post('/statuses', [StatusController::class, 'store'])->name('statuses.store');
    Route::delete('/statuses/{status}', [StatusController::class, 'destroy'])->name('statuses.destroy');
    Route::post('/statuses/{status}/view', [StatusController::class, 'view'])->name('statuses.view');
    Route::post('/statuses/{status}/like', [StatusController::class, 'like'])->name('statuses.like');

    // My interests (likes, saves, copies, shares)
    Route::get('/interests', [InterestController::class, 'show'])->name('interests.show');
    Route::post('/quotes/{quote}/track', [InterestController::class, 'track'])->name('quotes.track');

    // Welcome tour finished / skipped
    Route::post('/tour/complete', [TourController::class, 'complete'])->name('tour.complete');
});
