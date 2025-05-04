{{--
    =====================================
    Settings Appearance View (Mobile Responsive)
    =====================================
    - Responsive paddings and text sizes
    - Radio group is full-width on mobile
    =====================================
--}}
<section class="w-full p-4 sm:p-0">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="w-full">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
