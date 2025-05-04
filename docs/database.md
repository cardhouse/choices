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
    - Nullable for anonymous lists
    - Set when an anonymous list is claimed by a registered user
  - title
  - description
  - claimed_at (datetime)
    - Set when an anonymous list is claimed by a registered user
    - Used to prevent duplicate claims
  - is_anonymous (boolean, default: false)
    - True for lists created by anonymous users
    - Set to false when claimed by a registered user
  - created_at
  - updated_at

### decision_list_items
- Stores individual items within a decision list
- Fields:
  - id (primary key)
  - list_id (foreign key to decision_lists)
  - title
  - description
  - created_at
  - updated_at

### matchups
- Stores pairwise comparisons between items
- Fields:
  - id (primary key)
  - list_id (foreign key to decision_lists)
  - item_a_id (foreign key to decision_list_items)
  - item_b_id (foreign key to decision_list_items)
  - status (enum: pending, completed)
  - created_at
  - updated_at

### votes
- Stores user votes on matchups
- Fields:
  - id (primary key)
  - matchup_id (foreign key to matchups)
  - user_id (foreign key to users)
    - Nullable for anonymous votes
    - Set when an anonymous list is claimed
  - chosen_item_id (foreign key to decision_list_items)
  - session_token (string)
    - Used to track anonymous votes
    - Used to associate votes with user after registration
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

### User
- hasMany DecisionList
- hasMany Vote

### DecisionList
- belongsTo User
- hasMany DecisionListItem
- hasMany Matchup
- hasMany Vote (through Matchup)

### DecisionListItem
- belongsTo DecisionList
- hasMany MatchupA (as item_a)
- hasMany MatchupB (as item_b)
- hasMany Vote (as chosen_item)

### Matchup
- belongsTo DecisionList
- belongsTo ItemA (DecisionListItem)
- belongsTo ItemB (DecisionListItem)
- hasMany Vote

### Vote
- belongsTo Matchup
- belongsTo User (nullable)
- belongsTo ChosenItem (DecisionListItem)

## Indexes

### users
- email (unique)

### decision_lists
- user_id
- is_anonymous
- claimed_at

### decision_list_items
- list_id

### matchups
- list_id
- item_a_id
- item_b_id
- status

### votes
- matchup_id
- user_id
- chosen_item_id
- session_token

## Foreign Key Constraints

### decision_lists
- user_id references users(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE

### decision_list_items
- list_id references decision_lists(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE

### matchups
- list_id references decision_lists(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE
- item_a_id references decision_list_items(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE
- item_b_id references decision_list_items(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE

### votes
- matchup_id references matchups(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE
- user_id references users(id)
  - ON DELETE SET NULL
  - ON UPDATE CASCADE
- chosen_item_id references decision_list_items(id)
  - ON DELETE CASCADE
  - ON UPDATE CASCADE 