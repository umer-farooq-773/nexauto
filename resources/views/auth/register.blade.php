<x-layouts.login page-title="{{__('language.sign_up')}}" sub-title="{{__('language.sign_up_sub_title')}}">

    <x-auth.form form-action="{{ route('register') }}">

        <div class="">
            <x-auth.input-field type="text" name="first_name" id="first_name" required="true"
                place="{{ __('language.first_name_placeholder') }}" val="" extraclasses=""
                label="{{ __('language.first_name_label') }}" />
        </div>

        <div class="">
            <x-auth.input-field type="text" name="last_name" id="last_name" required="true"
                place="{{ __('language.last_name_placeholder') }}" val="" extraclasses=""
                label="{{ __('language.last_name_label') }}" />
        </div>

        <div class="">
            <x-auth.input-field type="email" name="email" id="email" required="true"
                place="{{ __('language.email_placeholder') }}" val="" extraclasses=""
                label="{{ __('language.email_label') }}" />
        </div>

        <div class="">
            <x-auth.input-field type="text" name="phone_number" id="phone_number" required="true"
                place="{{ __('language.phone_number_placeholder') }}" val="" extraclasses=""
                label="{{ __('language.phone_number_label') }}" />
        </div>

        <div class="">
            <div class="password-field position-relative">
                <x-auth.input-field type="password" name="password" id="password" required="true"
                    place="{{ __('language.password_placeholder') }}" val=""
                    label="{{ __('language.password_label') }}" />
                <span class="position-absolute top-50 end-0 translate-middle-y me-3"><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>

        <div class="">
            <div class="password-field position-relative">
                <x-auth.input-field type="password" name="password_confirmation" id="password_confirmation"
                    required="true" place="{{ __('language.password_placeholder') }}" val=""
                    label="{{ __('language.confirm_password_label') }}" />
                <span class="position-absolute top-50 end-0 translate-middle-y me-3"><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>

        <div class="">
            <div class="form-check">
                <input class="form-check-input @error('terms_condition') is-invalid @enderror" type="checkbox" value="1"
                    name="terms_condition" id="terms_condition">
                <label class="form-check-label small text-muted" for="terms_condition">
                    {{ __('language.agree_terms') }}
                    <x-auth.href-link link-href="#" link-value="{{ __('language.terms_of_use') }}" />
                </label>
            </div>
        </div>

        <div class="d-grid">
            <x-auth.input-button btn-class="" btn-type="submit"
                btn-value="{{ __('language.create_account_button') }}" />
        </div>

    </x-auth.form>

    <div class="text-center mt-2">
        <p class="mb-0">
            {{ __('language.already_have_account') }}
            <x-auth.href-link link-href="{{ route('login') }}" link-value="{{ __('language.sign_in') }}"
                link-class="fw-bold text-decoration-none" extra="style='color: var(--primary) !important;'" />
        </p>
    </div>
</x-layouts.login>