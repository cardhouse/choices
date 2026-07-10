<?php

namespace App\Livewire;

use App\Models\DecisionList;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dashboard Livewire component.
 *
 * Shows user stats, quick actions, their own lists, and lists they joined.
 */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    /**
     * Render the dashboard with user stats and lists.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $user = Auth::user();

        $lists = DecisionList::withCount('items')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $joinedLists = $user->participatingLists()
            ->withCount('items')
            ->orderByDesc('list_participants.created_at')
            ->get();

        $lists->each(fn ($list) => $list->status = $this->statusFor($list));
        $joinedLists->each(fn ($list) => $list->status = $this->statusFor($list));

        return view('dashboard', [
            'totalDecisions' => $lists->filter(fn ($list) => $list->isVotingClosed())->count(),
            'listsCount' => $lists->count(),
            'votesCount' => Vote::where('user_id', $user->id)->count(),
            'lists' => $lists,
            'joinedLists' => $joinedLists,
        ]);
    }

    /**
     * Display status for a list: completed, shared, open, or anonymous.
     */
    protected function statusFor(DecisionList $list): string
    {
        if ($list->isVotingClosed()) {
            return 'completed';
        }

        if ($list->is_anonymous && ! $list->claimed_at) {
            return 'anonymous';
        }

        return $list->isShared() ? 'shared' : 'open';
    }
}
