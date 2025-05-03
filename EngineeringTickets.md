# Engineering Ticket Breakdown

## Epic: Data Modeling & Migrations

### Ticket 1: Create Migration for Lists Table
**Summary**  
Set up the `lists` table to store both anonymous and user-owned lists, including expiration and claim tracking fields.

**Acceptance Criteria**
- Migration creates table `lists` with:
  - `id`: primary key
  - `user_id`: nullable foreign key to `users`
  - `name`: string
  - `expires_at`: nullable timestamp (used for anonymous list expiration)
  - `claimed_at`: nullable timestamp (set when anonymous user registers)
  - `timestamps`: Laravel standard

**Technical Details**
- Use `Schema::create('lists', function (Blueprint $table) { ... })`.
- Add foreign key constraint to `user_id` referencing `users(id)`.
- Set up `expires_at` and `claimed_at` as nullable timestamps.
- Index `expires_at` for scheduled deletions lookup.

---

### Ticket 2: Create Migration for Items Table
**Summary**  
Add an `items` table to store entries associated with each list.

**Acceptance Criteria**
- Table has:
  - `id`: primary key
  - `list_id`: foreign key to `lists`
  - `label`: string (item label)
  - `timestamps`

**Technical Details**
- Use `foreignId('list_id')->constrained()->onDelete('cascade')`.
- Validate `label` length/format in the model later.

---

### Ticket 3: Create Migration for Matchups Table
**Summary**  
Store round-robin pairings between items in a list.

**Acceptance Criteria**
- Table has:
  - `id`
  - `list_id`: foreign key
  - `item_a_id` and `item_b_id`: both foreign keys to `items`
  - `winner_item_id`: nullable FK (set post-vote)
  - `timestamps`

**Technical Details**
- Ensure uniqueness on `(item_a_id, item_b_id)` to avoid duplicates.
- Use `onDelete('cascade')` on all foreign keys.

---

### Ticket 4: Create Migration for Votes Table
**Summary**  
Store individual votes for matchups.

**Acceptance Criteria**
- Columns:
  - `id`
  - `matchup_id`: FK
  - `user_id`: nullable FK
  - `session_token`: nullable string (for anonymous tracking)
  - `chosen_item_id`: FK
  - `timestamps`

**Technical Details**
- Either `user_id` or `session_token` must be present—enforce in app logic.
- Use `foreignId` for all relationships.
- Consider indexing `matchup_id` and `session_token`.

---

### Ticket 5: Create Migration for Share Codes Table
**Summary**  
Allow generating unique codes to share a list.

**Acceptance Criteria**
- Table includes:
  - `id`
  - `list_id`: FK
  - `code`: string, unique
  - `expires_at`: nullable timestamp
  - `timestamps`

**Technical Details**
- Use `unique('code')` index.
- Short, human-readable code generation via a service (covered in later ticket).

---

## Epic: Domain & Application Services

### Ticket 6: Implement `MatchupGenerator::forList(List $list)`
**Summary**  
This service generates all unique matchups (head-to-head pairings) between items in a list. This enables a precomputed voting experience.

**Acceptance Criteria**
- For any list with N items, generate all (N × (N - 1)) / 2 unique matchups.
- Create a `matchups` row for each item pair.
- Ensure no duplicate or reverse matchups exist.

**Technical Details**
- Create a service class `MatchupGenerator` in `App\Services`.
- Use nested loops (or a generator) to pair items where `item_a.id < item_b.id`.
- Save each pairing with `list_id`, `item_a_id`, `item_b_id`.
- Wrap the entire operation in a DB transaction.

```php
foreach ($items as $i => $itemA) {
    for ($j = $i + 1; $j < count($items); $j++) {
        $itemB = $items[$j];
        Matchup::create([
            'list_id' => $list->id,
            'item_a_id' => $itemA->id,
            'item_b_id' => $itemB->id,
        ]);
    }
}
```

**Dependencies / Notes**
- Must be run after all list items are saved.
- Add unit test with sample list of items.

---

### Ticket 7: Implement `ScoreCalculator::forList(List $list)`
**Summary**  
Calculates final scores from matchups and votes, applying tiebreaker logic to rank items.

**Acceptance Criteria**
- For a given list:
  - Tally wins for each item based on `winner_item_id` in matchups.
  - Apply deterministic tiebreaker (e.g., label alphabetically).
  - Return ordered results.

**Technical Details**
- Create `ScoreCalculator` in `App\Services`.
- Fetch all matchups with non-null `winner_item_id`.
- Count wins using a `Collection::countBy()` pattern.
- For tie resolution, sort by `label` or `created_at`.

```php
$wins = collect($matchups)->countBy('winner_item_id')->all();
```

**Dependencies / Notes**
- Ensure data integrity: votes must resolve to a `winner_item_id`.

---

### Ticket 8: Implement `ShareListService::generateCode(List $list, ?DateTime $expiresAt)`
**Summary**  
Generates a short, unique code that can be used to share a list. Optionally expires.

**Acceptance Criteria**
- Generates a `share_codes` entry with:
  - Unique 6–8 character code
  - Associated list_id
  - Optional expiration

**Technical Details**
- Use a custom alphabet generator to build a code from a fixed charset (e.g., base62).
- Retry on collision if code already exists.

```php
do {
    $code = Str::random(8);
} while (ShareCode::where('code', $code)->exists());
```

- Save record with `ShareCode::create(...)`.

**Dependencies / Notes**
- Optional: enforce one active share code per list.
- Consider exposing as a method on `List` model via delegation.

---

### Ticket 9: Implement List Deletion Job and Claim Cancellation
**Summary**  
Implements the delayed job that deletes an anonymous list unless it has been claimed by a user. Cancels deletion on claim.

**Acceptance Criteria**
- Dispatch a job (`DeleteUnclaimedList`) 30 minutes after list creation.
- If `claimed_at` is set before the job runs, it should skip deletion.
- If user registers and claims list, delete the job or mark it as cancelled.

**Technical Details**
- Create `DeleteUnclaimedList` job (queued).
- Add logic to check if list has been claimed.
- Store dispatched job ID (e.g., in `cache`, DB, or on list if needed).
- On claiming (e.g., in `ClaimListService`), cancel/delete the job.

**Dependencies / Notes**
- If using Laravel Horizon or Redis-backed queue, leverage tags or cancel tokens.
- For testing: simulate list claim before job executes.

---

## Epic: Policies, Gates & FormRequests

### Ticket 10: Define `ListPolicy`
**Summary**  
Control user access to list actions including view, create, update, delete, and claim.

**Acceptance Criteria**
- Policy includes methods:
  - `view(User $user, List $list)`
  - `create(User $user)`
  - `update(User $user, List $list)`
  - `delete(User $user, List $list)`
  - `claim(User $user, List $list)`
- Policies should enforce that users can only access their own lists or claim unowned ones.

**Technical Details**
- Generate with `php artisan make:policy ListPolicy --model=List`.
- Add logic to check ownership or null `user_id` in `claim`.

---

### Ticket 11: Define `ItemPolicy` and `VotePolicy`
**Summary**  
Ensure items and votes are only accessed or created in valid contexts.

**Acceptance Criteria**
- `ItemPolicy` must verify that item’s `list.user_id` matches current user.
- `VotePolicy` must ensure:
  - Matchup belongs to list user has access to
  - Chosen item belongs to that matchup

**Technical Details**
- Create policies with `artisan make:policy`.
- Use policy chaining with relationships in conditions.

---

### Ticket 12: Register Policies in `AuthServiceProvider`
**Summary**  
Ensure all created policies are registered with Laravel’s gate system.

**Acceptance Criteria**
- Policies mapped to models inside `AuthServiceProvider::$policies`.

**Technical Details**
- Add mappings like:
```php
protected $policies = [
    List::class => ListPolicy::class,
    Item::class => ItemPolicy::class,
    Vote::class => VotePolicy::class,
];

---

Ticket 13: Create StoreListRequest

Summary
Form request to validate new list submissions, including number of items and content format.

Acceptance Criteria
	•	Items must be:
	•	An array of 2–100 elements
	•	Each item must be a non-empty string between 1 and 255 chars

Technical Details
	•	Create with artisan make:request StoreListRequest.
	•	Use array|min:2|max:100 and nested validation.

⸻

Ticket 14: Create VoteRequest

Summary
Validates submitted vote to ensure matchup and item are both valid and related.

Acceptance Criteria
	•	matchup_id must exist and belong to a list user can access
	•	chosen_item_id must match either item_a_id or item_b_id for the matchup

Technical Details
	•	Inject Matchup model and validate related logic inside authorize() or rules() if needed.
	•	Consider using a custom validation rule or Rule::in(...).