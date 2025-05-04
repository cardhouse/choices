# Routes

This document outlines the routes defined in the Choices application.

## Web Routes

Located in `routes/web.php`

### Public Routes
- `/` - GET: Landing page (LandingPage component)
- `/lists/create` - GET: Create new list (CreateList component)
- `/lists/examples` - GET: Show example lists (ShowExamples component)
- `/lists/{list}` - GET: Show list details (ShowList component)
- `/lists/{list}/vote` - GET: Show voting interface (VoteRound component)
- `/timer` - GET: Timer page

### Authenticated Routes
- `/dashboard` - GET: User dashboard (Dashboard component)
- `/settings/profile` - GET: User profile settings (Profile component)
- `/settings/password` - GET: Password settings (Password component)
- `/settings/appearance` - GET: Appearance settings (Appearance component)
- `/lists/{list}/results` - GET: Show voting results (RankedResults component)

### Authentication Routes
Located in `routes/auth.php`
- `/login` - GET: Show login form
- `/login` - POST: Handle login
- `/register` - GET: Show registration form
- `/register` - POST: Handle registration
- `/logout` - POST: Handle logout
- `/password/reset` - GET: Show password reset form
- `/password/reset` - POST: Handle password reset

## Route Middleware

The application uses several middleware to protect routes:

1. `auth` - Ensures user is authenticated
2. `verified` - Ensures user's email is verified

## Route Model Binding

The application uses route model binding for the following models:
- DecisionList

## Rate Limiting

API routes are rate-limited to prevent abuse:
- Authentication routes: 5 attempts per minute
- Other API routes: 60 requests per minute

## Livewire Components

The application uses Livewire components for dynamic functionality:

1. **LandingPage**
   - Home page component
   - Public access

2. **Dashboard**
   - User dashboard
   - Requires authentication

3. **List Components**
   - CreateList: Create new decision lists
   - ShowList: Display list details
   - ShowExamples: Display example lists
   - VoteRound: Handle voting process
   - RankedResults: Display voting results

4. **Settings Components**
   - Profile: Manage user profile
   - Password: Change password
   - Appearance: Customize appearance

## API Routes

Located in `routes/api.php`

### Authentication Routes
- `/api/auth/register` - POST: Register new user
- `/api/auth/login` - POST: Login user
- `/api/auth/logout` - POST: Logout user
- `/api/auth/refresh` - POST: Refresh authentication token

### Decision List Routes
- `/api/lists` - GET: List user's decision lists
- `/api/lists` - POST: Create new decision list
- `/api/lists/{list}` - GET: Get decision list details
- `/api/lists/{list}` - PUT: Update decision list
- `/api/lists/{list}` - DELETE: Delete decision list

### Item Routes
- `/api/lists/{list}/items` - GET: List items in decision list
- `/api/lists/{list}/items` - POST: Create new item
- `/api/lists/{list}/items/{item}` - GET: Get item details
- `/api/lists/{list}/items/{item}` - PUT: Update item
- `/api/lists/{list}/items/{item}` - DELETE: Delete item

### Voting Routes
- `/api/lists/{list}/matchups` - GET: Get available matchups
- `/api/lists/{list}/matchups/{matchup}/vote` - POST: Submit vote
- `/api/lists/{list}/results` - GET: Get voting results

### Share Routes
- `/api/lists/{list}/share` - POST: Generate share code
- `/api/lists/{list}/share/{code}` - DELETE: Revoke share code
- `/api/share/{code}` - GET: Get shared decision list details 