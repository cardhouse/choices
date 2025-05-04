<?php

namespace App\Livewire\Auth;

use App\Models\DecisionList;
use App\Models\User;
use App\Services\ListClaimService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        // Check if there's an anonymous list to claim
        $listId = session('anonymous_list_id');
        if ($listId) {
            try {
                $list = DecisionList::find($listId);
                if ($list && $list->is_anonymous && ! $list->claimed_at) {
                    app(ListClaimService::class)->claimList($list, $user);
                    
                    Log::info('Anonymous list claimed after registration', [
                        'list_id' => $list->id,
                        'user_id' => $user->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to claim anonymous list during registration', [
                    'list_id' => $listId,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // If there's an intended URL, redirect there, otherwise go to dashboard
        $intendedUrl = session('intended_url');
        $this->redirect($intendedUrl ?? route('dashboard', absolute: false), navigate: true);
    }
}
