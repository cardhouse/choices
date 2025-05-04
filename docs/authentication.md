# Authentication

This document describes the authentication system used in the Choices application.

## Authentication Flow

### Anonymous User Flow
1. User creates a list anonymously or starts voting on a shared list
2. System tracks votes using session token
3. Upon completing votes:
   - System stores intended URL (results page)
   - System stores anonymous list ID in session
   - System stores message explaining registration requirement
   - Redirects to login page
4. During registration:
   - System automatically claims the list for the user:
     - List's user_id is set to the authenticated user
     - List is marked as no longer anonymous
     - List's claimed_at timestamp is set
     - Anonymous votes are associated with the user
   - System automatically redirects to intended URL
   - User can view their voting results
   - List appears in user's dashboard

### Registration
- Users can register with:
  - Name
  - Email
  - Password
- Email verification is optional
- After registration:
  - User is automatically logged in
  - Any anonymous lists are claimed
  - User is redirected to their intended destination

### Login
- Users can log in with:
  - Email
  - Password
- Remember me functionality available
- After login:
  - User is redirected to their intended destination

### Logout
1. User clicks logout
2. System destroys session
3. Redirects to login page

### Password Reset
- Users can request password reset
- Reset links expire after 60 minutes
- Rate limiting on reset requests
- Secure token generation and validation

## Security Features

### Password Hashing
- Uses Laravel's bcrypt hashing
- Minimum password length: 8 characters
- Requires at least one:
  - Uppercase letter
  - Lowercase letter
  - Number
  - Special character

### Session Management
- Secure session cookies
- Session timeout: 2 hours
- Remember me functionality
- Session regeneration on login
- Anonymous user session tracking:
  - Vote persistence using session tokens
  - Intended URL storage for post-registration redirect
  - Anonymous list ID storage for claiming
  - Flash messages for registration prompts
  - Session data preserved during authentication

### CSRF Protection
- CSRF token required for all forms
- Token validation on all POST/PUT/DELETE requests
- Token regeneration on authentication

### Rate Limiting
- Login attempts: 5 per minute
- Password reset requests: 3 per hour
- API authentication: 60 requests per minute

## User Model

Located in `app/Models/User.php`

### Properties
- name
- email
- password (hashed)
- remember_token
- email_verified_at

### Methods
- `isVerified()`: Check email verification
- `hasVerifiedEmail()`: Alias for isVerified
- `markEmailAsVerified()`: Mark email as verified
- `sendEmailVerificationNotification()`: Send verification email

## Authentication Controllers

Located in `app/Http/Controllers/Auth/`

### RegisterController
- Handles user registration
- Validates input
- Creates user account
- Sends verification email

### LoginController
- Handles user login
- Validates credentials
- Creates session
- Handles remember me

### VerificationController
- Handles email verification
- Verifies email tokens
- Updates user status

### PasswordResetController
- Handles password reset
- Sends reset links
- Validates tokens
- Updates passwords

## Middleware

### Authentication Middleware
- `auth`: Ensures user is authenticated
- `guest`: Ensures user is not authenticated
- `verified`: Ensures email is verified
- `claim-anonymous-list`: Automatically claims anonymous lists after authentication

### Authorization Middleware
- `can:view,list`: Check list view permission
- `can:edit,list`: Check list edit permission

## API Authentication

### Token-based Authentication
- Personal access tokens
- Token expiration
- Token revocation
- Scope-based permissions

### Sanctum Integration
- Token generation
- Token validation
- Token revocation
- Token scopes

## Social Authentication

### Available Providers
- Google
- GitHub
- Twitter

### Integration Flow
1. User selects provider
2. Redirects to provider
3. User authorizes
4. Creates/updates account
5. Logs in user

## Security Best Practices

### Password Security
- Strong password requirements
- Password hashing
- Password reset flow
- Password change notification

### Session Security
- Secure cookies
- Session timeout
- Session regeneration
- Remember me security

### API Security
- Token-based authentication
- Rate limiting
- CORS configuration
- Input validation

## Testing

### Authentication Tests
- Registration tests
- Login tests
- Password reset tests
- Email verification tests

### Security Tests
- CSRF protection tests
- Rate limiting tests
- Session security tests
- API authentication tests

### Account Management
- Users can update profile information
- Password changes require current password
- Account deletion with confirmation
- Data export functionality 