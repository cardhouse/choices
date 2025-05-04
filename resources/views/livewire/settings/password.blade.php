{{--
    =====================================
    Settings Password View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Form and buttons are full-width on mobile
    =====================================
--}}
<section class="w-full p-4 sm:p-0">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6 w-full">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                class="w-full"
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                class="w-full"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                class="w-full"
            />

            <div class="flex flex-col sm:flex-row items-center gap-4">
                <div class="flex items-center justify-end w-full sm:w-auto">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
