# Database Structure

This document outlines the database schema and relationships in the Choices application.

## Tables

### users
- Primary table for user authentication and profile information
- Fields:
  - id (primary key)
  - name
  - email
  - email_verified_at
  - password
  - remember_token
  - created_at
  - updated_at

### decision_lists
- Stores the main decision lists created by users
- Fields:
  - id (primary key)
  - user_id (foreign key to users)
  - title
  - description
  - claimed_at (datetime)
  - is_anonymous (boolean, default: false)
  - voting_completed_at (datetime)
  - created_at
  - updated_at

### decision_list_items
- Stores items within a decision list
- Fields:
  - id (primary key)
  - list_id (foreign key to decision_lists)
  - label
  - created_at
  - updated_at

### matchups
- Stores the matchups between items for voting
- Fields:
  - id (primary key)
  - list_id (foreign key to decision_lists)
  - item1_id (foreign key to decision_list_items)
  - item2_id (foreign key to decision_list_items)
  - created_at
  - updated_at

### votes
- Stores user votes for matchups
- Fields:
  - id (primary key)
  - matchup_id (foreign key to matchups)
  - user_id (foreign key to users)
  - selected_item_id (foreign key to decision_list_items)
  - created_at
  - updated_at

### share_codes
- Stores share codes for decision lists
- Fields:
  - id (primary key)
  - list_id (foreign key to decision_lists)
  - code
  - expires_at
  - created_at
  - updated_at

## Relationships

1. User to Decision Lists (One-to-Many)
   - A user can have many decision lists
   - A decision list belongs to one user

2. Decision List to Items (One-to-Many)
   - A decision list can have many items
   - An item belongs to one decision list

3. Decision List to Matchups (One-to-Many)
   - A decision list can have many matchups
   - A matchup belongs to one decision list

4. Matchup to Items (Many-to-One)
   - A matchup has two items (item1 and item2)
   - An item can be in many matchups

5. User to Votes (One-to-Many)
   - A user can have many votes
   - A vote belongs to one user

6. Matchup to Votes (One-to-Many)
   - A matchup can have many votes
   - A vote belongs to one matchup

7. Decision List to Share Codes (One-to-Many)
   - A decision list can have many share codes
   - A share code belongs to one decision list

## Indexes

- Primary keys on all tables
- Foreign key indexes on all relationship fields
- Unique index on share_codes.code
- Index on votes.user_id and votes.matchup_id for performance
- Index on matchups.list_id for performance 