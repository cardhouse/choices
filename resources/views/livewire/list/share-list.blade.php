<div class="bg-white rounded-2xl shadow-lg p-4 sm:p-8 mb-8">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg sm:text-2xl font-bold text-gray-900">Share with Friends</h2>
        @if($list->isVotingClosed())
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700">Voting closed</span>
        @endif
    </div>

    @if($list->isVotingClosed())
        <p class="text-gray-500">Voting has closed on this list, so it can no longer be shared.</p>
    @elseif($activeCode)
        <p class="text-gray-500 mb-4">Friends can join with this code, or by following the link.</p>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-4"
             x-data="{ copied: false, copy(text) { navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => this.copied = false, 2000); } }">
            <div class="flex-grow flex items-center justify-center px-4 py-3 rounded-xl bg-gray-50 border border-gray-200">
                <span class="text-xl sm:text-2xl font-mono font-bold tracking-widest text-gray-900">{{ $activeCode->code }}</span>
            </div>
            <button type="button"
                    @click="copy('{{ route('lists.join.code', ['code' => $activeCode->code]) }}')"
                    class="inline-flex items-center justify-center px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">
                <span x-show="!copied">Copy Invite Link</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
        </div>

        @if($activeCode->expires_at)
            <p class="text-sm text-gray-500 mb-4">
                Voting closes automatically {{ $activeCode->expires_at->diffForHumans() }}
                ({{ $activeCode->expires_at->format('M j, Y g:ia') }}).
            </p>
        @endif

        <div class="flex flex-col sm:flex-row gap-3">
            <button wire:click="closeVoting" wire:confirm="Close voting and reveal the results? This can't be undone."
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">
                Close Voting &amp; Reveal Results
            </button>
            <button wire:click="revokeCode" wire:confirm="Revoke this code? Friends who already joined keep voting, but no one new can join."
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-red-200 text-red-600 font-semibold hover:bg-red-50 transition">
                Revoke Code
            </button>
        </div>
    @else
        <p class="text-gray-500 mb-4">
            Generate a code so friends can join and vote. The winner is decided by
            total victories across everyone's votes.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
            <div class="flex-grow">
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Voting duration</label>
                <select wire:model="duration" id="duration"
                        class="w-full rounded-xl border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 px-3 py-2 transition">
                    <option value="none">No deadline — I'll close it myself</option>
                    <option value="1day">1 day</option>
                    <option value="3days">3 days</option>
                    <option value="1week">1 week</option>
                </select>
            </div>
            <button wire:click="generateCode"
                    class="inline-flex items-center justify-center px-6 py-2 rounded-xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">
                Generate Share Code
            </button>
        </div>

        @if($list->votes()->exists())
            <div class="mt-6 pt-4 border-t border-gray-100">
                <button wire:click="closeVoting" wire:confirm="Close voting and reveal the results? This can't be undone."
                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-green-600 text-white font-semibold shadow hover:bg-green-700 transition">
                    Close Voting &amp; Reveal Results
                </button>
            </div>
        @endif
    @endif
</div>
