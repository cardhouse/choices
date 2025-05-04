<x-layouts.app :title="__('Dashboard')">
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="mb-10 text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-2">Dashboard</h1>
                <p class="text-lg text-gray-500">Welcome to your dashboard. Here you can manage your lists, view analytics, and more.</p>
            </div>
            <div class="grid auto-rows-min gap-8 md:grid-cols-3 mb-10">
                <div class="relative aspect-video overflow-hidden rounded-2xl shadow-lg bg-white border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-2xl shadow-lg bg-white border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-2xl shadow-lg bg-white border border-neutral-200 dark:border-neutral-700">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
            <div class="relative h-full flex-1 overflow-hidden rounded-2xl shadow-lg bg-white border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
    </div>
</x-layouts.app>
