Epic: Data Modeling & Migrations.

Ticket 1: Create Migration for Lists Table

Summary
Set up the lists table to store both anonymous and user-owned lists, including expiration and claim tracking fields.

Acceptance Criteria
	•	Migration creates table lists with:
	•	id: primary key
	•	user_id: nullable foreign key to users
	•	name: string
	•	expires_at: nullable timestamp (used for anonymous list expiration)
	•	claimed_at: nullable timestamp (set when anonymous user registers)
	•	timestamps: Laravel standard

Technical Details
	•	Use Schema::create('lists', function (Blueprint $table) { ... }).
	•	Add foreign key constraint to user_id referencing users(id).
	•	Set up expires_at and claimed_at as nullable timestamps.
	•	Index expires_at for scheduled deletions lookup.

⸻

Ticket 2: Create Migration for Items Table

Summary
Add an items table to store entries associated with each list.

Acceptance Criteria
	•	Table has:
	•	id: primary key
	•	list_id: foreign key to lists
	•	label: string (item label)
	•	timestamps

Technical Details
	•	Use foreignId('list_id')->constrained()->onDelete('cascade').
	•	Validate label length/format in the model later.

⸻

Ticket 3: Create Migration for Matchups Table

Summary
Store round-robin pairings between items in a list.

Acceptance Criteria
	•	Table has:
	•	id
	•	list_id: foreign key
	•	item_a_id and item_b_id: both foreign keys to items
	•	winner_item_id: nullable FK (set post-vote)
	•	timestamps

Technical Details
	•	Ensure uniqueness on (item_a_id, item_b_id) to avoid duplicates.
	•	Use onDelete('cascade') on all foreign keys.

⸻

Ticket 4: Create Migration for Votes Table

Summary
Store individual votes for matchups.

Acceptance Criteria
	•	Columns:
	•	id
	•	matchup_id: FK
	•	user_id: nullable FK
	•	session_token: nullable string (for anonymous tracking)
	•	chosen_item_id: FK
	•	timestamps

Technical Details
	•	Either user_id or session_token must be present—enforce in app logic.
	•	Use foreignId for all relationships.
	•	Consider indexing matchup_id and session_token.

⸻

Ticket 5: Create Migration for Share Codes Table

Summary
Allow generating unique codes to share a list.

Acceptance Criteria
	•	Table includes:
	•	id
	•	list_id: FK
	•	code: string, unique
	•	expires_at: nullable timestamp
	•	timestamps

Technical Details
	•	Use unique('code') index.
	•	Short, human-readable code generation via a service (covered in later ticket).