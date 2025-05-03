## Epic: Results Display & Analytics

### Ticket 18: Results Matrix View
**Summary**  
Display a full matrix showing all item matchups and the outcomes of each.

**Acceptance Criteria**
- Table/grid showing:
  - Rows and columns labeled with item names
  - Cell value shows:
    - Win/loss/tie result (e.g., ✅ / ❌ / =)
    - Optional vote count

**Technical Details**
- Blade or Livewire component (`ResultsMatrix`)
- Retrieve all `Matchups` for the list
- Index results into a 2D array for efficient lookup
- Apply conditional formatting to indicate outcome
- Can optionally load vote counts via relationship

---

### Ticket 19: Ranked Results View
**Summary**  
Display final ranked order of items after voting ends.

**Acceptance Criteria**
- Items shown in order of total wins
- Tiebreaker logic applies consistently
- Display:
  - Rank number
  - Item label
  - Win count

**Technical Details**
- Leverage `ScoreCalculator::forList($list)`
- Blade component or Livewire component (`RankedResults`)
- Present sorted array with optional visual indicators (e.g., medals, icons)

---

### Ticket 20: Results Access Policy
**Summary**  
Restrict result visibility based on list ownership or settings.

**Acceptance Criteria**
- Anonymous lists: results shown immediately after voting
- Authenticated lists:
  - Results shown to list owner anytime
  - Shared lists: results accessible via share code
- Enforce via policy or middleware

**Technical Details**
- Extend `ListPolicy::viewResults()` logic
- If accessed via share code, bypass user requirement
- Wire result views to use appropriate gate or service-level checks

---