<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Identifies who is voting: an authenticated user or an anonymous session.
 */
readonly class Voter
{
    private function __construct(
        public ?int $userId,
        public ?string $sessionToken,
    ) {}

    public static function user(User $user): self
    {
        return new self($user->id, null);
    }

    public static function session(string $token): self
    {
        return new self(null, $token);
    }

    /**
     * The voter for the current request: the authenticated user, or the
     * session id for guests.
     */
    public static function current(): self
    {
        return Auth::check()
            ? self::user(Auth::user())
            : self::session(session()->getId());
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }

    /**
     * Constrain a votes query to this voter's votes.
     */
    public function scopeVotes(Builder $query): Builder
    {
        return $this->isAuthenticated()
            ? $query->where('user_id', $this->userId)
            : $query->where('session_token', $this->sessionToken);
    }
}
