# Project Specifications Document

## Project Name

Decision Helper Application

## Project Overview

The Decision Helper application allows users to create lists of items to facilitate decision-making through a "round-robin" voting mechanism. Items are presented head-to-head, and users vote to determine their preferences. The application supports collaborative voting, detailed result analytics, and shareable lists.

## Functional Requirements

### 1. User Authentication & Authorization

* Utilizes existing Laravel authentication system.
* Users can create, manage, and delete their own lists.
* Laravel policies determine user access rights.

### 2. List Management

* **Anonymous Users**:

  * Can create temporary lists (2–100 items).
  * Results displayed immediately upon voting completion.
  * Lists are scheduled for deletion (30-minute delayed job).
  * Anonymous users have the option to register and claim their list, canceling scheduled deletion.

* **Authenticated Users**:

  * Create, view, manage, and delete lists.
  * Can share lists using alphanumeric codes (uppercase, excluding confusing characters like O/0, I/1).
  * Can reopen past lists for new voting sessions.

### 3. Voting Mechanism

* Items presented in a randomized round-robin format (each item competes exactly once against every other item).
* Users must choose between two presented items each round.
* Progress indicator shown (numerically or via a progress bar).
* Voting state saved across user sessions and devices for logged-in users.

### 4. Results and Scoring

* Simple point-based system (each selection equals one point).
* Winner determined by total points:

  * Tiebreaker (two items): head-to-head matchup results.
  * Multiple-way tie or unresolved tie: random selection clearly indicated.

### 5. Sharing and Collaboration

* Authenticated users can share lists via generated alphanumeric codes.
* Shared lists have an optional voting duration, determined by the creator.
* Voting can be manually closed by the creator.
* Codes deleted automatically after voting concludes.

### 6. Analytics and Results Visualization

* **Public Users**:

  * View basic results (rankings without detailed points).
* **List Creators**:

  * Detailed analytics available:

    * Total votes per item.
    * Matchup outcomes displayed as a detailed matrix.
    * Number of voters participating in each list.

## Technical Requirements

* **Framework**: Laravel 12 (authentication included).
* **Frontend/UI**: Blade/Livewire with Tailwind CSS.
* **Database**: MySQL (default Laravel migrations).
* **Queues & Jobs**: Laravel Queues for delayed deletions.

## User Experience & Design

* Clean, minimalistic UI inspired by PollUnit.
* Easy-to-understand voting interactions.
* Visually engaging and intuitive result presentations.

## Assumptions

* No CSV or PDF export functionality is required initially.
* List size limit set to 100 items (may change later).

## Constraints

* Voting restricted to logged-in users for shared lists.
* Anonymous list creation not shareable; must be claimed via registration to persist.

## Success Criteria

* Users can easily create and vote on decision lists.
* Reliable round-robin voting functionality.
* Effective sharing mechanism with clear and accessible analytics.

## Future Considerations

* Export functionality (CSV/PDF).
* Advanced analytics.
* List expiration notifications and reminders.
