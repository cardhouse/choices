<div class="flex flex-col gap-6">
    <x-auth-header 
        :title="__('View Your Results')" 
        :description="session('message') ?? __('Please register or login to view your voting results. Your votes have been saved and will be available after registration.')" 
    />

    <div class="flex flex-col gap-4">
        <flux:button 
            :href="route('register')" 
            variant="primary" 
            class="w-full"
            wire:navigate
        >
            {{ __('Create Account') }}
        </flux:button>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Already have an account?') }}
            <flux:link :href="route('login')" wire:navigate>
                {{ __('Log in') }}
            </flux:link>
        </div>
    </div>
</div> 