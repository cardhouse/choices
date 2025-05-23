{{--
    =====================================
    Landing Page (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Hero and features grid stack vertically on mobile
    =====================================
--}}
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-blue-100 to-teal-100">
    <!-- Hero Section -->
    <div class="pt-12 sm:pt-16 pb-6 sm:pb-8 px-2 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center">
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">
                    Make Better Decisions, Together
                </h1>
                <p class="max-w-2xl mx-auto text-base sm:text-xl text-gray-600 mb-6 sm:mb-8">
                    Create lists, vote head-to-head, and discover what matters most. Perfect for teams, friends, or your own decision-making process.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <button wire:click="createList" class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Your List
                    </button>
                    <button wire:click="viewExamples" class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 border border-gray-300 text-base font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        See Examples
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Grid -->
    <div class="py-8 sm:py-12 px-2 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                <!-- Feature 1: Easy List Creation -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Easy List Creation</h3>
                    <p class="text-sm sm:text-base text-gray-600">Create lists with 2-100 items. Perfect for any decision-making scenario, from dinner choices to project priorities.</p>
                </div>

                <!-- Feature 2: Round-Robin Voting -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Head-to-Head Voting</h3>
                    <p class="text-sm sm:text-base text-gray-600">Compare items directly in a round-robin format. Each choice gets fair consideration against every other option.</p>
                </div>

                <!-- Feature 3: Instant Results -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Instant Results</h3>
                    <p class="text-sm sm:text-base text-gray-600">See rankings update in real-time. Get clear insights with our simple point-based scoring system.</p>
                </div>

                <!-- Feature 4: Collaboration -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Collaborate</h3>
                    <p class="text-sm sm:text-base text-gray-600">Share your lists with unique codes. Perfect for group decisions and team prioritization.</p>
                </div>

                <!-- Feature 5: Analytics -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Detailed Analytics</h3>
                    <p class="text-sm sm:text-base text-gray-600">Get insights into voting patterns and preferences. Understand why certain choices came out on top.</p>
                </div>

                <!-- Feature 6: Quick Start -->
                <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Start Instantly</h3>
                    <p class="text-sm sm:text-base text-gray-600">No account required. Create anonymous lists and start voting immediately. Register later to save your results.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-8 sm:py-12 px-2 sm:px-6 lg:px-8 bg-blue-600 mt-8">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-4">
                Ready to Make Better Decisions?
            </h2>
            <p class="text-base sm:text-xl text-blue-100 mb-6 sm:mb-8">
                Start with your first list and see how easy decision-making can be.
            </p>
            <button wire:click="createList" class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 border border-transparent text-base sm:text-lg font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white">
                Create Your First List
            </button>
        </div>
    </div>
</div>
