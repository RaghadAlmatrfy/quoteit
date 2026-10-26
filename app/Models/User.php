<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Field;
use App\Support\MediaUpload;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'username', 'email', 'password', 'bio', 'specialty', 'interests', 'avatar_path', 'is_private', 'tour_completed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'interests' => 'array',
            'is_private' => 'boolean',
            'tour_completed_at' => 'datetime',
            'specialty' => Field::class,
        ];
    }

    /**
     * Give every new user a unique @username made from their name.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->username ??= static::uniqueUsername($user->name);
        });
    }

    public static function uniqueUsername(string $name): string
    {
        $base = Str::slug(Str::ascii($name), '_') ?: 'user';
        $username = $base;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $base.'_'.$counter++;
        }

        return $username;
    }

    /** Use @username in URLs: /users/raghad */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'favorites')->withTimestamps();
    }

    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'requester_id');
    }

    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'addressee_id');
    }

    /** The friendship row between this user and another one (in any direction). */
    public function friendshipWith(User $other): ?Friendship
    {
        return Friendship::between($this, $other)->first();
    }

    public function isFriendsWith(User $other): bool
    {
        return Friendship::between($this, $other)->where('status', Friendship::Accepted)->exists();
    }

    /** IDs of all accepted friends. @return array<int, int> */
    public function friendIds(): array
    {
        return Friendship::where('status', Friendship::Accepted)
            ->where(fn (Builder $query) => $query->where('requester_id', $this->id)->orWhere('addressee_id', $this->id))
            ->get()
            ->map(fn (Friendship $friendship) => $friendship->requester_id === $this->id ? $friendship->addressee_id : $friendship->requester_id)
            ->all();
    }

    /** @return Collection<int, User> */
    public function friends()
    {
        return static::whereIn('id', $this->friendIds())->orderBy('name')->get();
    }

    /** Can $viewer see this user's quotes? (public account, owner, or friend) */
    public function isVisibleTo(?User $viewer): bool
    {
        if (! $this->is_private) {
            return true;
        }

        return $viewer !== null && ($viewer->is($this) || $this->isFriendsWith($viewer));
    }

    public function avatarUrl(): string
    {
        return MediaUpload::url($this->avatar_path) ?? asset('images/profile.jpeg');
    }

    /** @return array<int, Field> */
    public function interestFields(): array
    {
        return collect($this->interests ?? [])
            ->map(fn (string $value) => Field::tryFrom($value))
            ->filter()
            ->values()
            ->all();
    }
}
