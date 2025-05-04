# Frontend

This document describes the frontend architecture and components of the Choices application.

## Technology Stack

- Laravel Blade for server-side templating
- Tailwind CSS for styling
- Alpine.js for interactivity
- Livewire for dynamic components
- Flux for state management

## Directory Structure

```
resources/
├── views/
│   ├── components/
│   │   └── [Reusable Blade components]
│   ├── layouts/
│   │   └── app.blade.php
│   ├── livewire/
│   │   ├── landing-page.blade.php
│   │   ├── dashboard.blade.php
│   │   ├── list/
│   │   │   ├── create-list.blade.php
│   │   │   ├── show-list.blade.php
│   │   │   ├── show-examples.blade.php
│   │   │   ├── vote-round.blade.php
│   │   │   └── ranked-results.blade.php
│   │   └── settings/
│   │       ├── profile.blade.php
│   │       ├── password.blade.php
│   │       └── appearance.blade.php
│   ├── partials/
│   │   └── [Partial Blade templates]
│   ├── flux/
│   │   └── [Flux state management files]
│   ├── dashboard.blade.php
│   ├── timer.blade.php
│   └── welcome.blade.php
├── css/
│   └── app.css
└── js/
    └── app.js
```

## Layouts

### App Layout (`layouts/app.blade.php`)
- Main application layout
- Includes navigation bar
- Responsive design
- Dark mode support
- Alpine.js integration
- Livewire scripts

## Livewire Components

### Landing Page
- Public home page
- Introduction to the application
- Call to action

### Dashboard
- User's decision lists
- Recent activity
- Quick actions

### List Components
1. Create List
   - Title and description input
   - Item management
   - Preview functionality

2. Show List
   - List details
   - Item display
   - Voting interface

3. Show Examples
   - Example decision lists
   - Demonstration of features

4. Vote Round
   - Side-by-side comparison
   - Vote submission
   - Progress tracking

5. Ranked Results
   - Final rankings
   - Vote statistics
   - Share options

### Settings Components
1. Profile
   - User information
   - Avatar management
   - Contact details

2. Password
   - Password change form
   - Security settings

3. Appearance
   - Theme selection
   - Dark mode toggle
   - UI preferences

## JavaScript

### Alpine.js Components
1. Navigation
   - Mobile menu toggle
   - User dropdown
   - Dark mode toggle

2. Forms
   - Dynamic validation
   - Real-time feedback
   - Auto-save functionality

3. UI Elements
   - Toast notifications
   - Modal dialogs
   - Loading indicators

### Flux State Management
- Centralized state
- Action dispatchers
- State reducers
- Event listeners

## Styling

### Tailwind CSS
- Custom color palette
- Responsive design
- Dark mode support
- Component-specific styles

### Custom CSS
- Animation keyframes
- Utility classes
- Print styles
- Dark mode overrides

## Assets

### Images
- Logo
- Icons
- Placeholder images
- Background patterns

### Fonts
- Primary font: Inter
- Monospace font: Fira Code
- Icon font: Heroicons

## Responsive Design

### Breakpoints
- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

### Mobile-First Approach
- Base styles for mobile
- Progressive enhancement
- Touch-friendly interactions
- Optimized performance

## Performance Optimization

1. Asset Loading
   - Lazy loading images
   - Deferred JavaScript
   - Critical CSS inlining

2. Caching
   - Browser caching
   - Service worker
   - Asset versioning

3. Code Splitting
   - Route-based splitting
   - Component-based splitting
   - Dynamic imports 

## Mobile Responsiveness

All major user-facing views in Choices are now fully mobile responsive, following Tailwind CSS best practices:

- **Responsive paddings and text sizes**: All layouts, tables, forms, and buttons use responsive classes (e.g., `px-2 sm:px-6`, `text-base sm:text-lg`) to ensure readability and usability on all devices.
- **Tables and grids**: All tables are wrapped in `overflow-x-auto` and use responsive paddings. Grids and flex layouts stack vertically on mobile (`grid-cols-1`, `flex-col`) and scale up for larger screens.
- **Buttons and actions**: Primary actions and buttons are full-width on mobile (`w-full`) and stack vertically where appropriate for easy tapping.
- **Forms**: All form fields and submit buttons are full-width on mobile for accessibility and ease of use.
- **Docblocks and inline comments**: All view files are documented with docblocks and inline comments to explain structure, rationale, and any trade-offs.

### Rationale
- The mobile-first approach ensures a seamless experience for users on any device.
- Touch-friendly targets and readable text sizes improve accessibility and usability.
- All changes are maintainable and easy to extend, thanks to clear documentation and Tailwind's utility-first design.

### Affected Views
- Dashboard
- All List views (create, show, vote, results, matrix, examples, registration prompt)
- Landing page
- Settings (profile, password, appearance)
- Welcome page

For more details, see the docblocks and inline comments in each Blade view file. 