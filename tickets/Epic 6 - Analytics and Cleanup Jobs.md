## Epic: Analytics & Cleanup Jobs

### Ticket 21: Track Vote Progress per List
**Summary**  
Enable tracking of how many matchups have been voted on per list, by user/session.

**Acceptance Criteria**
- Count of completed matchups for the user/session
- Percentage complete indicator
- Prevent double voting

**Technical Details**
- Query `votes` where `matchup_id` belongs to list
- For authenticated users: filter by `user_id`
- For anonymous users: filter by `session_token`
- Total matchups = (n × (n - 1)) / 2

---

### Ticket 22: Implement `DeleteAnonymousList` Job
**Summary**  
Deletes an unclaimed anonymous list 30 minutes after creation unless claimed.

**Acceptance Criteria**
- Job deletes:
  - List
  - Related items, matchups, votes (cascade)
- If `claimed_at` is set, it should **not** delete the list

**Technical Details**
- `php artisan make:job DeleteAnonymousList`
- Check `claimed_at` inside job’s `handle()` method
- Log actions for audit/debugging

---

### Ticket 23: Schedule Deletion with `ListDeletionService`
**Summary**  
Central service to schedule deletion job when a list is created anonymously.

**Acceptance Criteria**
- Dispatch `DeleteAnonymousList` job with 30-minute delay
- Store job ID (optional) for potential cancellation

**Technical Details**
- `App\Services\ListDeletionService`
- Accepts a `List` model
- Uses `dispatch(...)->delay(now()->addMinutes(30))`
- Cache job ID using `Cache::put('delete_job_' . $list->id, $jobId)`

---

### Ticket 24: Cancel Scheduled Deletion on Claim
**Summary**  
Cancels deletion job when user registers and claims their list.

**Acceptance Criteria**
- Fetches cached job ID (or tag)
- Cancels job before execution
- Sets `claimed_at` on list

**Technical Details**
- Add method to `ListDeletionService::cancelDeletion($list)`
- Delete cache key or call a cancel hook (if using Horizon or custom queue driver)
- Ensure idempotency

---

### Ticket 25: Purge Expired Share Codes
**Summary**  
Periodic cleanup of expired or invalid share codes to reduce clutter.

**Acceptance Criteria**
- Deletes `share_codes` where `expires_at < now()`
- Can be run via `schedule:run` or invoked manually

**Technical Details**
- Artisan command `php artisan make:command PurgeShareCodes`
- Add to `App\Console\Kernel::schedule()` for hourly/daily purge

---