<x-layouts.auth page-title="{{ __('language.create_new_staff_label') }}" sub-title="{{ __('language.create_new_staff_sub_title') }}">
    <x-auth.add-user-form route-action="{{ route('users.store') }}" />
</x-layouts.auth>
