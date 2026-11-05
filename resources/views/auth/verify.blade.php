<x-layouts.login page-title="{{ __('language.verify_account') }}"
    sub-title="{{__('language.verify_account_sub_title')}}">
    @if (session('resent'))
    <div class="alert alert-success p-3" role="alert">
        {{ __('language.resent') }}
    </div>
    @endif

    <p class="text-center">
        {{ __('language.check_email') }}
        {{ __('language.did_not_receive') }}
    </p>
    

    <x-auth.form form-action="{{ route('verification.resend') }}">
        <div class="d-grid">
            <x-auth.input-button btn-class="" btn-type="submit"
                btn-value="{{ __('language.click_to_resend') }}" />
        </div>
    </x-auth.form>
</x-layouts.login>