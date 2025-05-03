<?php

namespace App\Providers;

use App\Models\DecisionList;
use App\Models\Item;
use App\Models\Vote;
use App\Policies\ItemPolicy;
use App\Policies\ListPolicy;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DecisionList::class => ListPolicy::class,
        Item::class => ItemPolicy::class,
        Vote::class => VotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
