<?php

namespace App\Actions\Lists;

use App\Models\DecisionList;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates a list with its items and round-robin matchups. Lists created
 * without a user are anonymous and get scheduled for delayed deletion.
 */
class CreateList
{
    public function __construct(
        private GenerateMatchups $generateMatchups,
        private ScheduleListDeletion $scheduleListDeletion,
    ) {}

    /**
     * @param  array{title: string, description?: ?string, items: array<int, string>}  $data
     */
    public function handle(array $data, ?User $user): DecisionList
    {
        $list = DB::transaction(function () use ($data, $user) {
            $list = DecisionList::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'user_id' => $user?->id,
                'is_anonymous' => $user === null,
            ]);

            foreach ($data['items'] as $label) {
                $list->items()->create(['label' => trim($label)]);
            }

            $this->generateMatchups->handle($list);

            return $list;
        });

        if ($list->is_anonymous) {
            $this->scheduleListDeletion->handle($list);
        }

        return $list;
    }
}
