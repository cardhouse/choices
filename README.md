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

### Lists Table
- Stores both anonymous and user-owned lists
- Fields:
  - `id` (primary key)
  - `user_id` (nullable foreign key)
  - `name` (string)
  - `description` (nullable text)
  - `status` (enum: active, closed, completed)
  - `expires_at` (nullable timestamp)
  - `claimed_at` (nullable timestamp)
  - `timestamps`

### Items Table
- Stores entries associated with each list
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key)
  - `label` (string)
  - `description` (nullable text)
  - `timestamps`

### Matchups Table
- Stores round-robin pairings between items
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key)
  - `item_a_id` (foreign key)
  - `item_b_id` (foreign key)
  - `winner_item_id` (nullable foreign key)
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
  - `chosen_item_id` (foreign key)
  - `ip_address` (nullable string)
  - `user_agent` (nullable string)
  - `timestamps`

### Share Codes Table
- Stores unique codes for sharing lists
- Fields:
  - `id` (primary key)
  - `list_id` (foreign key)
  - `code` (unique string, 8 chars)
  - `expires_at` (nullable timestamp)
  - `timestamps`

## Technical Stack

- **Framework**: Laravel 12
- **Frontend**: Blade/Livewire with Tailwind CSS
- **Database**: MySQL
- **Queues**: Laravel Queues for delayed deletions

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

## Future Considerations

- Export functionality (CSV/PDF)
- Advanced analytics
- List expiration notifications
- Reminders for ongoing votes 