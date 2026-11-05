<x-layouts.login page-title="{{__('language.sign_in')}}" sub-title="{{__('language.sign_in_sub_title')}}">

    <x-auth.form form-action="{{ route('login') }}">
        <x-auth.input-field type="email" name="email" id="email" required="true"
            place="{{ __('language.email_placeholder') }}" val="" extraclasses=""
            label="{{ __('language.email_label') }}" />

        <div class="">
            <div class="password-field position-relative">
                <x-auth.input-field type="password" name="password" id="password" required="true"
                    place="{{ __('language.password_placeholder') }}" val=""
                    label="{{ __('language.password_label') }}" />
                <span class="position-absolute top-50 end-0 translate-middle-y me-3"><i class="bi bi-eye-slash passwordToggler"></i></span>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                <label class="form-check-label" for="exampleCheck1">Remember me</label>
            </div>
            @if (Route::has('password.request'))
            <div class="text-end ">
                <x-auth.href-link link-href="{{ route('password.request') }}"
                    link-value="{{ __('language.forgot_password') }}"
                    link-class="small text-decoration-none text-muted fw-bold" />
            </div>
            @endif
        </div>

        <div class="d-grid">
            <x-auth.input-button btn-class="" btn-type="submit" btn-value="{{ __('language.login_button') }}" />
        </div>
    </x-auth.form>

    @if (Route::has('register'))
    <div class="text-center mt-2">
        <p class="mb-0">
            {{ __('language.dont_have_account') }}
            <x-auth.href-link link-href="{{ route('register') }}" link-value="{{ __('language.sign_up') }}"
                link-class="fw-bold text-decoration-none" extra="style='color: var(--primary) !important;'" />
        </p>
    </div>
    @endif

</x-layouts.login>

