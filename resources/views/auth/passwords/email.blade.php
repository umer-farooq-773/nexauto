<x-layouts.login class="reset-password" page-title="{{__('language.reset_password')}}"
    sub-title="{{__('language.reset_password_sub_title')}}">

    @if (session('status'))
    <div class="alert alert-success p-3" role="alert">
        {{ session('status') }}
    </div>
    @endif

    <x-auth.form form-action="{{ route('password.email') }}">
        <x-auth.input-field type="email" name="email" id="email" required="true"
            place="{{ __('language.email_placeholder') }}" val="" extraclasses=""
            label="{{__('language.email_label')}}" />
        <p class="form-text small text-muted mt-2">We will send a password reset link to this email.</p>

        <div class="d-grid mt-3 mb-3">
            <x-auth.input-button btn-class="" btn-type="submit"
                btn-value="{{ __('language.send_reset_link') }}" />
        </div>
    </x-auth.form>

    <div class="text-center mt-4">
        <p class="mb-0">
            <i class="fas fa-arrow-left me-1"></i>
            <x-auth.href-link link-href="{{ route('login') }}" link-value="{{ __('language.back_to_login_page') }}"
                link-class="fw-bold text-decoration-none" extra="style='color: var(--primary) !important;'" />
        </p>
    </div>
</x-layouts.login>