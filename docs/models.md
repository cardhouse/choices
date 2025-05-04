# Models

This document describes the Eloquent models used in the Choices application and their relationships.

## User Model

Located in `app/Models/User.php`

The User model represents authenticated users in the system. It extends Laravel's default User model and includes:

### Relationships
- `decisionLists()`: HasMany relationship with DecisionList
- `votes()`: HasMany relationship with Vote

### Methods
- Standard Laravel authentication methods
- Custom methods for managing user preferences and settings

## DecisionList Model

Located in `app/Models/DecisionList.php`

The DecisionList model represents a collection of items that users can vote on.

### Properties
- `claimed_at`: DateTime when the list was claimed
- `is_anonymous`: Boolean indicating if the list is anonymous
- `voting_completed_at`: DateTime when voting was completed

### Relationships
- `user()`: BelongsTo relationship with User
- `items()`: HasMany relationship with DecisionListItem
- `matchups()`: HasMany relationship with Matchup
- `shareCodes()`: HasMany relationship with ShareCode

### Methods
- `scheduleDeletion()`: Schedules the list for deletion if it is anonymous

## DecisionListItem Model

Located in `app/Models/DecisionListItem.php`

The DecisionListItem model represents individual items within a decision list.

### Properties
- `label`: The item's label

### Relationships
- `list()`: BelongsTo relationship with DecisionList

## Matchup Model

Located in `app/Models/Matchup.php`

The Matchup model represents a pairing of two items for voting.

### Relationships
- `list()`: BelongsTo relationship with DecisionList
- `item1()`: BelongsTo relationship with DecisionListItem
- `item2()`: BelongsTo relationship with DecisionListItem
- `votes()`: HasMany relationship with Vote

### Methods
- `getWinner()`: Determines the winning item based on votes
- `isComplete()`: Checks if voting is complete for this matchup

## Vote Model

Located in `app/Models/Vote.php`

The Vote model represents a user's vote in a matchup.

### Relationships
- `user()`: BelongsTo relationship with User
- `matchup()`: BelongsTo relationship with Matchup
- `selectedItem()`: BelongsTo relationship with DecisionListItem

## ShareCode Model

Located in `app/Models/ShareCode.php`

The ShareCode model represents shareable links for decision lists.

### Relationships
- `list()`: BelongsTo relationship with DecisionList

### Methods
- `isExpired()`: Checks if the share code has expired
- `generateCode()`: Generates a new share code

## Model Events and Observers

The application uses model events and observers to handle various automated tasks:

1. DecisionList
   - After creation: Automatically creates matchups
   - After update: Checks voting completion

2. Vote
   - After creation: Updates matchup and decision list status

3. ShareCode
   - Before creation: Generates unique code
   - Before save: Sets expiration date

## Model Validation Rules

Each model includes validation rules for creating and updating records:

1. DecisionList
   - Title: required, string, max:255
   - Description: nullable, string
   - is_anonymous: boolean

2. DecisionListItem
   - label: required, string, max:255

3. Vote
   - selected_item_id: required, exists:decision_list_items,id

4. ShareCode
   - code: required, string, unique
   - expires_at: required, date 