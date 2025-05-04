<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ open: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Choices') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!--
        =====================================
        Choices Navigation Bar
        =====================================
        - Improved spacing between logo and links
        - Conditional rendering of Dashboard/Home links
        - Guest users see only Login/Register
        - Mobile menu with user actions
        =====================================
    -->
    <nav class="bg-white shadow-sm border-b border-gray-200" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo and Main Links -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-blue-600 font-bold text-xl whitespace-nowrap">Choices</a>
                    @auth
                        <!-- Authenticated: Show Dashboard link only -->
                        <div class="hidden sm:flex gap-6">
                            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                        </div>
                    @else
                        <!-- Guest: Show Home link only -->
                        <div class="hidden sm:flex gap-6">
                            <a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600">Home</a>
                        </div>
                    @endauth
                </div>

                <!-- Desktop User Menu or Auth Links -->
                <div class="hidden sm:flex items-center gap-4">
                    @auth
                        <!--
                            =====================================
                            User Dropdown (Desktop)
                            =====================================
                            - Shows user name, dashboard, settings, logout
                            =====================================
                        -->
                        <div class="relative" x-data="{ dropdownOpen: false }">
                            <button @click="dropdownOpen = !dropdownOpen" class="flex items-center text-gray-600 hover:text-gray-800">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="ml-2 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 011.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.25 8.29a.75.75 0 01-.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-md z-20">
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Dashboard</a>
                                <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Settings</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Log Out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest: Only show Login/Register -->
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm text-gray-700 hover:text-gray-900">Register</a>
                        @endif
                    @endauth
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex sm:hidden items-center">
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!--
            =====================================
            Mobile Menu
            =====================================
            - Collapses into vertical menu
            - Shows correct links for auth/guest
            - User can log out from mobile
            =====================================
        -->
        <div class="sm:hidden" x-show="open" @click.away="open = false">
            <div class="pt-2 pb-3 space-y-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-base text-gray-700 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-base text-gray-700 hover:bg-gray-100">Settings</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-base text-gray-700 hover:bg-gray-100">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-base text-gray-700 hover:bg-gray-100">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block px-4 py-2 text-base text-gray-700 hover:bg-gray-100">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto py-12 px-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html> 