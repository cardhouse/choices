<?php

namespace App\Jobs;

use App\Models\DecisionList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to delete an unclaimed decision list after a specified delay.
 *
 * This job is dispatched when an anonymous list is created and will delete
 * the list after 30 minutes unless it has been claimed by a registered user.
 */
class DeleteUnclaimedList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param  DecisionList  $list  The list to potentially delete
     */
    public function __construct(
        public DecisionList $list
    ) {}

    /**
     * Execute the job.
     *
     * This method checks if the list is still unclaimed before deleting it.
     * If the list has been claimed, the job will exit without performing any action.
     */
    public function handle(): void
    {
        // If the list has been claimed, don't delete it
        if ($this->list->claimed_at !== null) {
            Log::info('Skipping deletion of claimed list', [
                'list_id' => $this->list->id,
                'claimed_at' => $this->list->claimed_at,
            ]);

            return;
        }

        // Delete the list and log the action
        $this->list->delete();

        Log::info('Deleted unclaimed list', [
            'list_id' => $this->list->id,
            'created_at' => $this->list->created_at,
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<string>
     */
    public function tags(): array
    {
        return ['list:'.$this->list->id, 'delete_unclaimed'];
    }
}
