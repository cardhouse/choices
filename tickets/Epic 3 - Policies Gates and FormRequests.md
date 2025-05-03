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