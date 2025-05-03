# Specification Review

## Authentication & Authorization

- Leveraging Laravel's built-in auth and Policies is spot on. I'd recommend defining a clear `ListPolicy` (and related `ItemPolicy`, `VotePolicy`) with methods for `view`, `create`, `update`, `delete`, and a `claim` action for anonymous lists.
- Consider whether you need role/permission granularity beyond "authenticated vs anonymous" (e.g. admin oversight, read-only collaborators) and plan for a permissions package (e.g. Spatie).

## Anonymous vs Authenticated Lists

- **Ephemeral Lists**: instead of hard-deleting at 30 minutes, model a nullable `claimed_at` and an `expires_at` timestamp.
- **Cancellation of Deletion**: store the queued job's ID (or dispatch via a "cancelable" mechanism) so that when a user claims, you immediately cancel the pending delete job.
- Enforce the "must register to share" rule in both Policy and business logic (e.g. in a `ShareListService`).

## Voting Mechanism

- Round-robin can be precomputed: on list creation, generate a `matchups` table (each row = two item IDs + status). This avoids on-the-fly pairing logic in Livewire.
- Persist user progress: for logged-in users, tie votes to `user_id`; for guests, to a temporary session token stored in the `lists` table.

## Results & Scoring

- Point system is straightforward: each vote row increments a counter.
- Tiebreakers: encode "head-to-head result" in your `matchups` table; for unresolved multi-way ties, randomize in code and log the method used so results are reproducible.

## Sharing & Collaboration

- Custom alphanumeric codes: implement via `Str::random()` over a custom alphabet (upper-case minus confusing chars).
- Store code uniqueness in DB with a unique index and reroll on collision.

## Analytics & Visualization

- Public vs Creator views: guard your API endpoints/controllers with Policies so that anonymous users only see aggregate ranks.
- Matrix display: consider building a Livewire component that fetches a JSON payload of the full results matrix and renders via a simple table or chart library.

---

# Foundational Technical Approach

## 1. Architecture & Folder Structure

```text
app/
├─ Domain/            # Entities, ValueObjects, DomainServices
├─ Models/            # Eloquent models (List, Item, Matchup, Vote, ShareCode)
├─ Policies/          # ListPolicy, ItemPolicy, VotePolicy
├─ Http/
│   ├─ Controllers/   # API or Blade controllers
│   └─ Requests/      # FormRequests (StoreListRequest, VoteRequest)
├─ Livewire/          # Components grouped by feature
│   ├─ List/           # CreateList, ShareList, ManageList
│   ├─ Voting/         # VoteRound, VoteProgress
│   └─ Results/        # ResultsMatrix, AnalyticsDashboard
├─ Services/          # Application services (MatchupGenerator, ScoreCalculator, ShareListService)
├─ Repositories/      # Interfaces + Eloquent implementations
└─ Jobs/              # DeleteAnonymousList, CancelDeleteJob
```

## 2. Data Modeling & Migrations

- **lists**: `id`, `user_id` nullable, `name`, `expires_at` nullable, `created_at`…
- **items**: `id`, `list_id`, `label`, `created_at`…
- **matchups**: `id`, `list_id`, `item_a_id`, `item_b_id`, `winner_item_id` nullable, `created_at`…
- **votes**: `id`, `matchup_id`, `user_id` nullable, `session_token` nullable, `chosen_item_id`, `created_at`…
- **share_codes**: `id`, `list_id`, `code` unique, `expires_at` nullable, `created_at`…

Enforce foreign keys and indexes on `(list_id, user_id)`, `(matchup_id)`, and unique on `code`.

## 3. Domain & Application Layers

### Domain Services

- `MatchupGenerator::forList(List $list): void` — precomputes all pairings.
- `ScoreCalculator::forList(List $list): Collection` — returns point totals, tiebreaker logic.

### Application Services

- `ShareListService::generateCode(List $list, \DateTime $expiresAt = null)`: handles code creation.
- `ListDeletionService::scheduleDeletion(List $list)`: dispatches delayed `DeleteAnonymousList` job.

> Use constructor injection for repositories and domain services to keep controllers/Livewire slim.

## 4. Livewire Component Organization

- **CreateList**  
  1. Step-by-step UI: collect items, validate count (2–100).  
  2. On submit, trigger `MatchupGenerator`.

- **VoteRound**  
  - Loads next matchup by session or user; persists vote via `VoteRequest`.  
  - Emits progress events; parent component shows progress bar.

- **ResultsMatrix**  
  - Fetches full vote data (via service) and displays a Tailwind-styled matrix.

> Encapsulate UI state in each component; use Alpine.js sparingly for minor interactivity (e.g. progress animations).

## 5. Policies, Gates & FormRequests

### FormRequests

- `StoreListRequest`: validate items array size and values.  
- `VoteRequest`: validate `matchup_id` belongs to list and `chosen_item_id` matches one of the two.

### Policies

- `view`, `update`, `delete` on `List` based on ownership or share code validity.  
- Deny "share" for anonymous until claimed.

## 6. Queues & Jobs

- **DeleteAnonymousList**  
  - Delayed 30 minutes; checks `claimed_at`; if still null, soft/hard-delete list and cascade.

- **CancelDeleteJob**  
  - Triggered on claim: cancels the pending delete job or flips a flag.

> Configure `QUEUE_CONNECTION=redis` (or database) and set up Horizon for monitoring.

## 7. Events, Broadcasting & Real-time Updates

- Fire `VoteRecorded` event on each vote; optionally broadcast via Pusher/Laravel Websockets to update a shared progress bar in real time.  
- Use Livewire's built-in polling or `->on('voteUpdated')` to refresh results without a full page reload.

## 8. Testing Strategy

- **Unit Tests** (Pest) for `MatchupGenerator`, `ScoreCalculator`, service layer.  
- **Feature Tests** for API endpoints and policies using HTTP and `actingAs`.  
- **Livewire Tests** via `livewire()` helper to simulate user flows: list creation → voting → results.  
- **Browser Tests** (optional) with Laravel Dusk for end-to-end UI verification.

> **Factories** for List, Item, Matchup, and Vote should cover typical and edge cases (ties, randomization).