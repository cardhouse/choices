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

