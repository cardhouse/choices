<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timer Examples</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Count Up Timer (Default)</h1>
            <livewire:timer />
        </div>

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Count Down Timer (10 minutes)</h1>
            <livewire:timer :timestamp="now()->addSeconds(10)->timestamp" direction="down" />
        </div>

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold text-center mb-6">Count Up Timer (Starting from 5 minutes)</h1>
            <livewire:timer :timestamp="now()->subMinutes(5)->timestamp" direction="up" />
        </div>
    </div>
    @livewireScripts
</body>
</html> 