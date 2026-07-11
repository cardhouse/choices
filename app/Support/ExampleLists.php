<?php

namespace App\Support;

/**
 * Pre-built example lists shown on the examples page and offered as
 * starting points when creating a new list.
 */
class ExampleLists
{
    /**
     * @return array<int, array{title: string, description: string, items: array<int, string>}>
     */
    public static function all(): array
    {
        return [
            [
                'title' => 'Dinner Menu Planning',
                'description' => 'Deciding on next week\'s dinner menu',
                'items' => ['Pizza', 'Sushi', 'Tacos', 'Pasta', 'Salad', 'Burgers'],
            ],
            [
                'title' => 'Weekend Activity',
                'description' => 'What should we do this weekend?',
                'items' => ['Movie Night', 'Hiking', 'Board Games', 'Beach Trip', 'Shopping'],
            ],
            [
                'title' => 'Project Priorities',
                'description' => 'Ranking features for next sprint',
                'items' => ['User Authentication', 'Payment Integration', 'Search Feature', 'Analytics Dashboard'],
            ],
        ];
    }
}
