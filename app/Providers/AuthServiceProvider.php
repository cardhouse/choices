<?php

namespace App\Providers;

use App\Models\DecisionList;
use App\Models\DecisionListItem;
use App\Models\Vote;
use App\Policies\DecisionListItemPolicy;
use App\Policies\ListPolicy;
use App\Policies\VotePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DecisionList::class => ListPolicy::class,
        DecisionListItem::class => DecisionListItemPolicy::class,
        Vote::class => VotePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('viewResults', [ListPolicy::class, 'viewResults']);
    }
}
