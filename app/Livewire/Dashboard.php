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
 * Shows user stats, quick actions, and a table of their lists.
 *
 * @property-read int $totalDecisions Total number of completed decisions (lists with completed status)
 * @property-read int $listsCount Number of lists created by the user
 * @property-read int $votesCount Number of votes cast by the user
 * @property-read \Illuminate\Database\Eloquent\Collection $lists User's lists with status and item count
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

        // Get all lists for the user, with item count and status
        $lists = DecisionList::withCount('items')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // Count completed lists as decisions made
        $totalDecisions = $lists->where('voting_completed_at', '!=', null)->count();
        $listsCount = $lists->count();
        $votesCount = Vote::where('user_id', $user->id)->count();

        // Add a status property to each list for display
        $lists->each(function ($list) {
            if ($list->voting_completed_at) {
                $list->status = 'completed';
            } elseif ($list->is_anonymous && !$list->claimed_at) {
                $list->status = 'anonymous';
            } else {
                $list->status = 'pending';
            }
        });

        return view('dashboard', [
            'totalDecisions' => $totalDecisions,
            'listsCount' => $listsCount,
            'votesCount' => $votesCount,
            'lists' => $lists,
        ]);
    }
} 