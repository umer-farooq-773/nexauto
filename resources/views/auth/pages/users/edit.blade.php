<x-layouts.auth page-title="{{ __('language.user_edit_title') }}" sub-title="Update user account details">
    <x-auth.add-user-form route-action="{{ route('users.update', $data?->id) }}" :data="$data" :is-edit="true" />
</x-layouts.auth>
