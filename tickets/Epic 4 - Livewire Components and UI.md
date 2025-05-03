## Epic: Livewire Components & UI

### Ticket 15: Livewire Component – `CreateList`
**Summary**  
Interactive multi-step form for users to create a new list and add items.

**Acceptance Criteria**
- Supports:
  - Step 1: List metadata (name)
  - Step 2: Add 2–100 items (form inputs)
- On submit:
  - Saves list and items
  - Triggers `MatchupGenerator::forList()`
- Supports both anonymous and authenticated users

**Technical Details**
- `php artisan make:livewire CreateList`
- Use Livewire state to persist list + items across steps
- On final step submission:
  - Validate using `StoreListRequest`
  - Create `List` and `Item` records
  - Call `MatchupGenerator` service
- For anonymous: defer user association

---

### Ticket 16: Livewire Component – `ClaimList`
**Summary**  
Allows anonymous users to register or log in and claim a list they created.

**Acceptance Criteria**
- Accepts:
  - Either a share code
  - Or a pre-linked anonymous list (via session)
- On successful claim:
  - Updates `user_id` and `claimed_at`
  - Cancels scheduled deletion

**Technical Details**
- `php artisan make:livewire ClaimList`
- Accepts share code or uses preloaded `List` from session
- Calls `ListDeletionService::cancelDeletion(...)`
- Persists authenticated user ID to the list

---

### Ticket 17: Livewire Component – `VoteRound`
**Summary**  
Display one matchup at a time for user to vote on, and progress through list.

**Acceptance Criteria**
- Loads the next unvoted matchup for the user/session
- Shows head-to-head items (A vs B)
- User picks one — records vote
- After final matchup, redirects to results page

**Technical Details**
- `php artisan make:livewire VoteRound`
- Use session or `user_id` to track voting state
- Submit vote using `VoteRequest`
- On vote:
  - Create `Vote`
  - Update `Matchup.winner_item_id` if this is the deciding vote
- After all votes complete, redirect to results

---