<?php

namespace App\Livewire\Auth;

use App\Models\DecisionList;
use App\Services\ListClaimService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['email', 'password']), $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Check if there's an anonymous list to claim
        $listId = session('anonymous_list_id');
        if ($listId) {
            try {
                $list = DecisionList::find($listId);
                if ($list && $list->is_anonymous && ! $list->claimed_at) {
                    app(ListClaimService::class)->claimList($list, Auth::user());
                    
                    Log::info('Anonymous list claimed after login', [
                        'list_id' => $list->id,
                        'user_id' => Auth::id(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to claim anonymous list during login', [
                    'list_id' => $listId,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // If there's an intended URL, redirect there, otherwise go to dashboard
        $intendedUrl = session('intended_url');
        $this->redirect($intendedUrl ?? route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
