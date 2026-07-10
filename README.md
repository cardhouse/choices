# Decision Helper Application

A Laravel-based application that helps users make decisions through a round-robin voting mechanism. Users can create lists of items, vote on them in head-to-head matchups, and analyze the results.

## Features

### User Authentication & Authorization
- Utilizes Laravel's built-in authentication system
- Users can create, manage, and delete their own lists
- Access control through Laravel policies

### List Management
- **Anonymous Users**:
  - Create temporary lists (2-100 items)
  - Immediate results display
  - 30-minute expiration (with option to claim via registration)
- **Authenticated Users**:
  - Full list management capabilities
  - Share lists via unique codes
  - Reopen past lists for new voting sessions

### Voting Mechanism
- Randomized round-robin format
- Each item competes exactly once against every other item
- Progress tracking
- Session persistence for logged-in users

### Results and Scoring
- Point-based system (1 point per selection)
- Clear tiebreaker rules
- Detailed analytics for list creators

### Sharing and Collaboration
- Unique alphanumeric share codes
- Configurable voting duration
- Manual voting closure option
- Automatic code expiration

## Database Schema

### Decision Lists Table
- Stores both anonymous and user-owned lists
- Fields:
  - `id` (primary key)
  - `user_id` (nullable foreign key)
  - `title` (string)
  - `description` (nullable text)
  - `is_anonymous` (boolean, default: false)
  - `claimed_at` (nullable timestamp)
  - `voting_closes_at` (nullable timestamp — optional deadline set when sharing)
  - `voting_closed_at` (nullable timestamp — set when the owner closes voting)
  - `timestamps`

### List Participants Table
- Tracks users who joined a shared list by redeeming a share code
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `user_id` (foreign key to users)
  - `share_code_id` (nullable foreign key to share_codes)
  - `timestamps`
- Unique index on (`list_id`, `user_id`)

### Items Table
- Stores entries associated with each list
- Table name: `decision_list_items`
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `label` (string)
  - `description` (nullable text)
  - `timestamps`

### Matchups Table
- Stores round-robin pairings between items. Votes are the source of truth
  for outcomes; matchups carry no winner or status of their own.
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `item_a_id` (foreign key to decision_list_items)
  - `item_b_id` (foreign key to decision_list_items)
  - `round_number` (integer)
  - `timestamps`

### Votes Table
- Stores individual votes for matchups
- Fields:
  - `id` (primary key)
  - `matchup_id` (foreign key)
  - `user_id` (nullable foreign key)
  - `session_token` (nullable string)
  - `chosen_item_id` (foreign key to decision_list_items)
  - `ip_address` (nullable string)
  - `user_agent` (nullable string)
  - `timestamps`

### Share Codes Table
- Stores unique codes for sharing lists
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `code` (unique string, 8 chars)
  - `expires_at` (nullable timestamp)
  - `deactivated_at` (nullable timestamp)
  - `timestamps`
- Indexes:
  - Unique index on `code`
  - Composite index on `list_id` and `deactivated_at`

## Technical Stack

- **Framework**: Laravel 12
- **Frontend**: Blade/Livewire with Tailwind CSS
- **Database**: SQLite (dev) / MySQL
- **Queues**: Laravel Queues (database driver) for delayed deletions

### Architecture: Actions

All mutations live in single-purpose Action classes under `app/Actions`.
Livewire components validate input (reusing the FormRequest rule definitions
in `app/Http/Requests`), check policies, call an action, and handle the UI
response. Actions enforce domain invariants and own the transaction.

- `Actions\Lists`: `CreateList`, `DeleteList`, `GenerateMatchups`,
  `ClaimAnonymousList`, `ScheduleListDeletion`
- `Actions\Voting`: `CastVote`, `CloseVoting`
- `Actions\Sharing`: `GenerateShareCode`, `RedeemShareCode`, `RevokeShareCode`

Read-side queries stay in `app/Services`:

- `ScoreCalculator`: ranks items by total victories (votes across all
  voters), with spec tiebreakers — two-way ties fall back to the tied items'
  head-to-head result, anything still tied is ordered by a deterministic
  random draw and flagged as such in the results.

## Model Relationships

### DecisionList
- Has many DecisionListItems
- Has many Matchups
- Belongs to User (optional)
- Has many ShareCodes

### DecisionListItem
- Belongs to DecisionList
- Has many Matchups (as item_a)
- Has many Matchups (as item_b)
- Has many Votes (through matchups)

### Matchup
- Belongs to DecisionList
- Belongs to DecisionListItem (as item_a)
- Belongs to DecisionListItem (as item_b)
- Belongs to DecisionListItem (as winner)
- Has many Votes

### Vote
- Belongs to Matchup
- Belongs to User (optional)
- Belongs to DecisionListItem (as chosen_item)

### ShareCode
- Belongs to DecisionList
- Scopes:
  - `active()`: Returns only active, non-expired codes

## Testing

### Database Configuration
- Uses SQLite in-memory database for testing
- Configured in `config/database.php` as `sqlite_testing` connection
- Test environment settings in `phpunit.xml`

### Model Factories

#### DecisionList Factory
- Default: Creates a list with random title and description
- States:
  - `anonymous()`: Creates an anonymous list without user
  - `claimed()`: Creates a list that has been claimed

#### DecisionListItem Factory
- Default: Creates an item with random label and optional description
- Automatically associates with a DecisionList

#### Matchup Factory
- Default: Creates a matchup between two items from the same list
- States:
  - `completed()`: Creates a matchup with a winner
  - `pending()`: Creates a pending matchup

#### Vote Factory
- Default: Creates a vote for a matchup with user
- States:
  - `anonymous()`: Creates a vote with session token instead of user

#### ShareCode Factory
- Default: Creates a share code with optional expiration
- States:
  - `expired()`: Creates an expired share code
  - `permanent()`: Creates a permanent share code

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy `.env.example` to `.env` and configure your environment
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```

## How Sharing Works

1. The owner opens their list and generates a share code (optionally with a
   voting deadline of 1 day / 3 days / 1 week).
2. Friends log in and enter the code at `/join` — or follow the invite link
   (`/join/{CODE}`) — which makes them a participant and drops them straight
   into voting.
3. Every participant votes through all head-to-head matchups at their own
   pace; votes can be changed until voting closes.
4. The owner closes voting manually (or the deadline passes). Share codes
   deactivate, and results unlock for all participants. The owner can peek at
   live standings any time; participants wait for the close.
5. The winner is the item with the most total victories across all voters.

Lists that are never shared close themselves when their single voter
finishes, so the solo flow ends at results immediately.

## Development Status

Feature-complete against the original spec:

- **Data model** — lists, items, matchups, votes, share codes, participants;
  per-voter voting with votes as the single source of truth.
- **Action layer** — all mutations as Action classes with unit tests;
  FormRequests own validation rules and are reused by the Livewire components.
- **Voting** — randomized round-robin per voter with progress tracking; votes
  persist per user (or per session for anonymous lists) and can be changed
  until close.
- **Sharing** — share codes + invite links, optional voting deadline, manual
  close, revocation, participant tracking, join page at `/join`.
- **Results** — victory totals across all voters, spec tiebreakers
  (head-to-head, then labeled random), owner-only analytics (voter count and
  head-to-head matrix). Results are gated until voting closes; owners can
  always peek.
- **Anonymous flow** — guest lists are scheduled for deletion after 30
  minutes and claimed automatically (with their votes) when the guest
  registers or logs in.
- **Authorization** — `ListPolicy` enforced in every list-facing component.

Run the test suite with `vendor/bin/pest` (requires `npm run build` first so
the Vite manifest exists).

## Future Considerations

- Export functionality (CSV/PDF)
- Advanced analytics
- List expiration notifications
- Reminders for ongoing votes 