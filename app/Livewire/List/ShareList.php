<?php

namespace App\Livewire\List;

use App\Actions\Sharing\GenerateShareCode;
use App\Actions\Sharing\RevokeShareCode;
use App\Actions\Voting\CloseVoting;
use App\Models\DecisionList;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Owner-only panel on the list page: mint/revoke share codes, set an
 * optional voting deadline, and close voting.
 */
class ShareList extends Component
{
    public DecisionList $list;

    /**
     * Voting duration choice for a new code: none, 1day, 3days, or 1week.
     */
    public string $duration = 'none';

    public function mount(DecisionList $list): void
    {
        Gate::authorize('manageVoting', $list);

        $this->list = $list;
    }

    public function generateCode(): void
    {
        Gate::authorize('manageVoting', $this->list);

        app(GenerateShareCode::class)->handle($this->list, $this->expiresAt());

        $this->list->refresh();
    }

    public function revokeCode(): void
    {
        Gate::authorize('manageVoting', $this->list);

        app(RevokeShareCode::class)->handle($this->list);

        $this->list->refresh();
    }

    public function closeVoting()
    {
        Gate::authorize('manageVoting', $this->list);

        app(CloseVoting::class)->handle($this->list);

        return redirect()->route('lists.results', ['list' => $this->list]);
    }

    protected function expiresAt(): ?Carbon
    {
        return match ($this->duration) {
            '1day' => now()->addDay(),
            '3days' => now()->addDays(3),
            '1week' => now()->addWeek(),
            default => null,
        };
    }

    public function render()
    {
        return view('livewire.list.share-list', [
            'activeCode' => $this->list->activeShareCode(),
        ]);
    }
}
