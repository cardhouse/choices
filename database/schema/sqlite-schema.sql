CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "decision_lists"(
  "id" integer primary key autoincrement not null,
  "user_id" integer,
  "title" varchar not null,
  "description" text,
  "is_anonymous" tinyint(1) not null default '0',
  "claimed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  "voting_completed_at" datetime,
  foreign key("user_id") references "users"("id") on delete set null
);
CREATE INDEX "decision_lists_claimed_at_index" on "decision_lists"(
  "claimed_at"
);
CREATE INDEX "decision_lists_is_anonymous_index" on "decision_lists"(
  "is_anonymous"
);
CREATE TABLE IF NOT EXISTS "decision_list_items"(
  "id" integer primary key autoincrement not null,
  "list_id" integer not null,
  "label" varchar not null,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("list_id") references "decision_lists"("id") on delete cascade
);
CREATE INDEX "items_list_id_index" on "decision_list_items"("list_id");
CREATE TABLE IF NOT EXISTS "matchups"(
  "id" integer primary key autoincrement not null,
  "list_id" integer not null,
  "item_a_id" integer not null,
  "item_b_id" integer not null,
  "winner_item_id" integer,
  "status" varchar check("status" in('pending', 'completed', 'skipped')) not null default 'pending',
  "round_number" integer not null default '1',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("list_id") references "decision_lists"("id") on delete cascade,
  foreign key("item_a_id") references "decision_list_items"("id") on delete cascade,
  foreign key("item_b_id") references "decision_list_items"("id") on delete cascade,
  foreign key("winner_item_id") references "decision_list_items"("id") on delete set null
);
CREATE INDEX "matchups_list_id_index" on "matchups"("list_id");
CREATE INDEX "matchups_item_a_id_item_b_id_index" on "matchups"(
  "item_a_id",
  "item_b_id"
);
CREATE INDEX "matchups_status_index" on "matchups"("status");
CREATE INDEX "matchups_round_number_index" on "matchups"("round_number");
CREATE UNIQUE INDEX "matchups_list_id_item_a_id_item_b_id_unique" on "matchups"(
  "list_id",
  "item_a_id",
  "item_b_id"
);
CREATE TABLE IF NOT EXISTS "votes"(
  "id" integer primary key autoincrement not null,
  "matchup_id" integer not null,
  "user_id" integer,
  "session_token" varchar,
  "chosen_item_id" integer not null,
  "ip_address" varchar,
  "user_agent" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("matchup_id") references "matchups"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete set null,
  foreign key("chosen_item_id") references "decision_list_items"("id") on delete cascade
);
CREATE INDEX "votes_matchup_id_index" on "votes"("matchup_id");
CREATE INDEX "votes_user_id_index" on "votes"("user_id");
CREATE INDEX "votes_session_token_index" on "votes"("session_token");
CREATE INDEX "votes_chosen_item_id_index" on "votes"("chosen_item_id");
CREATE INDEX "votes_ip_address_index" on "votes"("ip_address");
CREATE UNIQUE INDEX "unique_user_vote" on "votes"("matchup_id", "user_id");
CREATE UNIQUE INDEX "unique_session_vote" on "votes"(
  "matchup_id",
  "session_token"
);
CREATE TABLE IF NOT EXISTS "share_codes"(
  "id" integer primary key autoincrement not null,
  "list_id" integer not null,
  "code" varchar not null,
  "expires_at" datetime,
  "deactivated_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("list_id") references "decision_lists"("id") on delete cascade
);
CREATE INDEX "share_codes_list_id_deactivated_at_index" on "share_codes"(
  "list_id",
  "deactivated_at"
);
CREATE UNIQUE INDEX "share_codes_code_unique" on "share_codes"("code");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2024_04_01_000000_create_lists_table',1);
INSERT INTO migrations VALUES(5,'2024_04_01_000001_create_items_table',1);
INSERT INTO migrations VALUES(6,'2024_04_01_000002_create_matchups_table',1);
INSERT INTO migrations VALUES(7,'2024_04_01_000003_create_votes_table',1);
INSERT INTO migrations VALUES(8,'2024_05_03_000000_create_share_codes_table',1);
INSERT INTO migrations VALUES(9,'2025_05_03_185722_rename_items_table_to_decision_list_items',1);
INSERT INTO migrations VALUES(10,'2025_05_03_194011_add_voting_completed_at_to_decision_lists_table',1);
