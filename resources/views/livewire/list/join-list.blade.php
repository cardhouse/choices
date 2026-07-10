<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white py-8 px-2 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto">
        <div class="mb-8 sm:mb-10 text-center">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 mb-2">Join a List</h1>
            <p class="text-base sm:text-lg text-gray-500">
                Enter the share code you received to start voting.
            </p>
        </div>

        <form wire:submit.prevent="join" class="bg-white rounded-2xl shadow-lg p-4 sm:p-8">
            <label for="code" class="block text-base sm:text-lg font-semibold text-gray-800 mb-2">Share Code</label>
            <input type="text" wire:model="code" id="code" maxlength="8" autocomplete="off"
                class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 sm:px-4 py-2 sm:py-3 text-lg sm:text-xl font-mono tracking-widest uppercase text-center transition placeholder-gray-400"
                placeholder="ABCD2345">
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit"
                class="mt-6 inline-flex items-center justify-center w-full px-6 py-3 rounded-xl bg-blue-600 text-white text-base sm:text-lg font-bold shadow-lg hover:bg-blue-700 transition">
                Join &amp; Vote
            </button>
        </form>
    </div>
</div>
