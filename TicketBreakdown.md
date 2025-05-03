Epic: Data Modeling & Migrations
	1.	Create lists table migration
	•	Columns: id, user_id (nullable FK), name, expires_at (nullable), claimed_at (nullable), timestamps
	2.	Create items table migration
	•	Columns: id, list_id (FK), label, timestamps
	3.	Create matchups table migration
	•	Columns: id, list_id (FK), item_a_id (FK), item_b_id (FK), winner_item_id (nullable FK), timestamps
	4.	Create votes table migration
	•	Columns: id, matchup_id (FK), user_id (nullable FK), session_token (nullable), chosen_item_id (FK), timestamps
	5.	Create share_codes table migration
	•	Columns: id, list_id (FK), code (unique), expires_at (nullable), timestamps

Epic: Domain & Application Services
	6.	Implement MatchupGenerator::forList(List $list)
	•	Precompute all item pairings into matchups
	7.	Implement ScoreCalculator::forList(List $list)
	•	Tally votes, apply tiebreaker logic, return sorted results
	8.	Implement ShareListService::generateCode(List $list, ?DateTime $expiresAt)
	•	Custom alphabet code, DB uniqueness check, expiration handling
	9.	Implement ListDeletionService::scheduleDeletion(List $list)
	•	Dispatch DeleteAnonymousList job with 30-minute delay
	10.	Implement cancellation logic in ListDeletionService::cancelDeletion(JobId $jobId)
	•	Hook this into “claim list” workflow

Epic: Policies, Gates & FormRequests
	11.	Define ListPolicy
	•	Methods: view, create, update, delete, claim
	12.	Define ItemPolicy & VotePolicy
	•	Ensure votes only on valid matchups, items only within owner’s lists
	13.	Register policies in AuthServiceProvider
	14.	Create StoreListRequest
	•	Validate items array count (2-100) and content
	15.	Create VoteRequest
	•	Validate matchup_id and chosen_item_id correspond

Epic: Livewire Components & UI
	16.	Livewire: CreateList component
	•	Multi-step form, item inputs, triggers MatchupGenerator
	17.	Livewire: ClaimList component
	•	Accepts share code or registration, calls cancelDeletion
	18.	Livewire: VoteRound component
	•	Loads next matchup, submits via VoteRequest, emits progress
	19.	Livewire: VoteProgress sub-component
	•	Displays numeric or bar progress (session or user-tied)
	20.	Livewire: ResultsMatrix component
	•	Renders full head-to-head table, consumes JSON from ScoreCalculator
	21.	Livewire: AnalyticsDashboard component
	•	Shows point totals, voter counts, tiebreaker info

Epic: Sharing & Collaboration
	22.	Controller/API: ShareListController@create
	•	Uses ShareListService to mint code, returns JSON
	23.	Livewire: ShareList component
	•	UI to set duration, display/share code, enforce policy

Epic: Queues & Jobs
	24.	Job: DeleteAnonymousList
	•	On handle: check claimed_at; delete list & cascade
	25.	Configure queue_connection & Horizon
	•	Set up Redis (or database) driver, monitoring

Epic: Events & Broadcasting
	26.	Event: VoteRecorded
	•	Dispatched in vote handler
	27.	Broadcasting setup
	•	Configure Pusher or laravel-websockets, channel for progress updates
	28.	Livewire listener
	•	Update progress/analytics in real time via $this->on('voteRecorded')

Epic: Testing
	29.	Unit tests for MatchupGenerator
	30.	Unit tests for ScoreCalculator
	31.	Feature tests for ShareListService & ListDeletionService
	32.	Livewire tests
	•	CreateList, VoteRound, ResultsMatrix, ShareList flows
	33.	Policy tests
	•	Ensure unauthorized access is denied for anonymous vs. claimed vs. owner
	34.	Optional Dusk end-to-end tests

Epic: CI/CD & Env Configuration
	35.	GitHub Actions: lint + Pest + Livewire tests on PR
	36.	Deploy workflow: staging → production
	•	Cache config/routes, migrate, queue:restart
