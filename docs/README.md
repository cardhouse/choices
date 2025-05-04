# Project Documentation

Welcome to the Choices project documentation. This documentation is designed to help developers understand the architecture, components, and functionality of the Choices application.

## Documentation Structure

1. [Project Overview](overview.md) - High-level overview of the project
2. [Database Structure](database.md) - Database schema and relationships
3. [Models](models.md) - Eloquent models and their relationships
4. [Controllers](controllers.md) - Application controllers and business logic
5. [Routes](routes.md) - API and web routes
6. [Frontend](frontend.md) - Frontend components and views
7. [Authentication](authentication.md) - Authentication system
8. [Deployment](deployment.md) - Deployment instructions

## Getting Started

To get started with the project:

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy `.env.example` to `.env` and configure your environment variables
4. Run migrations:
   ```bash
   php artisan migrate
   ```
5. Start the development server:
   ```bash
   php artisan serve
   npm run dev
   ```

## Contributing

Please read our [Contributing Guidelines](contributing.md) before submitting any changes. 