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
  - `timestamps`

### Items Table
- Stores entries associated with each list
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `label` (string)
  - `description` (nullable text)
  - `timestamps`

### Matchups Table
- Stores round-robin pairings between items
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key to decision_lists)
  - `item_a_id` (foreign key to items)
  - `item_b_id` (foreign key to items)
  - `winner_item_id` (nullable foreign key to items)
  - `status` (enum: pending, completed, skipped)
  - `round_number` (integer)
  - `timestamps`

### Votes Table
- Stores individual votes for matchups
- Fields:
  - `id` (primary key)
  - `matchup_id` (foreign key)
  - `user_id` (nullable foreign key)
  - `session_token` (nullable string)
  - `chosen_item_id` (foreign key to items)
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
- **Database**: MySQL
- **Queues**: Laravel Queues for delayed deletions
- **Services**:
  - `MatchupGenerator`: Creates round-robin matchups for lists
  - `ScoreCalculator`: Calculates and ranks items based on matchup results
  - `ShareListService`: Generates and manages unique share codes for lists

## Model Relationships

### DecisionList
- Has many Items
- Has many Matchups
- Belongs to User (optional)
- Has many ShareCodes

### Item
- Belongs to DecisionList
- Has many Matchups (as item_a)
- Has many Matchups (as item_b)
- Has many Votes (through matchups)

### Matchup
- Belongs to DecisionList
- Belongs to Item (as item_a)
- Belongs to Item (as item_b)
- Belongs to Item (as winner)
- Has many Votes

### Vote
- Belongs to Matchup
- Belongs to User (optional)
- Belongs to Item (as chosen_item)

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

#### Item Factory
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

## Development Status

### Epic 1 - Data Modeling & Migrations (Completed)
- Implemented all required database tables
- Added appropriate indexes and constraints
- Set up foreign key relationships
- Added additional fields for enhanced functionality

### Epic 2 - Domain & Application Services (In Progress)
- Implemented MatchupGenerator service
- Implemented ScoreCalculator service:
  - Calculates item rankings based on matchup wins
  - Handles tiebreakers using alphabetical ordering
  - Provides comprehensive error handling and logging
  - Includes full test coverage with various scenarios
- Implemented ShareListService:
  - Generates unique 8-character share codes using custom alphabet (excluding O/0, I/1)
  - Implements retry mechanism with max attempts for unique code generation
  - Uses database transactions to ensure data integrity
  - Supports optional expiration dates with proper DateTime handling
  - Automatically deactivates existing codes before generating new ones
  - Includes comprehensive error handling with custom exceptions
  - Full test coverage including:
    - Basic code generation
    - Code uniqueness verification
    - Expiration date handling
    - Existing code deactivation
    - Custom alphabet validation
    - Error handling for generation failures
- Implemented List Deletion and Claiming:
  - Automatic deletion of unclaimed anonymous lists after 30 minutes
  - Queued job system for handling delayed deletions
  - List claiming functionality for registered users
  - Transaction-based claiming process
  - Comprehensive error handling and validation
  - Full test coverage including:
    - Deletion scheduling
    - Job execution
    - List claiming
    - Error cases
- Added comprehensive test coverage with model factories
- Set up testing environment with in-memory SQLite database

## Future Considerations

- Export functionality (CSV/PDF)
- Advanced analytics
- List expiration notifications
- Reminders for ongoing votes 