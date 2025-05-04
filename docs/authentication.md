# Authentication

This document describes the authentication system used in the Choices application.

## Authentication Flow

### Registration
1. User submits registration form
2. System validates input
3. Creates new user account
4. Sends verification email
5. Redirects to login page

### Login
1. User submits login form
2. System validates credentials
3. Creates session
4. Redirects to dashboard

### Logout
1. User clicks logout
2. System destroys session
3. Redirects to login page

### Password Reset
1. User requests password reset
2. System sends reset link
3. User clicks link
4. User submits new password
5. System updates password
6. Redirects to login page

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

### CSRF Protection
- CSRF tokens on all forms
- VerifyCsrfToken middleware
- XSRF-TOKEN cookie

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