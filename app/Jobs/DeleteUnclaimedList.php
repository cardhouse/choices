<?php

namespace App\Jobs;

use App\Actions\Lists\DeleteList;
use App\Models\DecisionList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Deletes an anonymous list after its grace period unless it was claimed.
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
     * @param  DecisionList  $list  The list to potentially delete
     */
    public function __construct(
        public DecisionList $list
    ) {}

    public function handle(DeleteList $deleteList): void
    {
        if ($this->list->claimed_at !== null) {
            Log::info('Skipping deletion of claimed list', [
                'list_id' => $this->list->id,
                'claimed_at' => $this->list->claimed_at,
            ]);

            return;
        }

        $deleteList->handle($this->list);
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
