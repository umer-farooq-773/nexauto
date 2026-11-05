@props([
    'routeAction',
    'data' => null,
    'isEdit' => false,
])

@php
    $result = $data;
    $route = $routeAction;
    $currentRole = $result?->roles?->first();
@endphp

<x-auth.form form-id="UserForm" form-action="{{ $route }}" enctype="true" extra-class="align-items-start">
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="col-12 col-xl-8">
        <x-auth.card card-header="{{ $isEdit ? __('language.user_edit_title') : __('language.create_new_staff_label') }}">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-auth.input-field
                        type="text"
                        name="f_name"
                        id="f_name"
                        required="true"
                        place="{{ __('language.first_name_placeholder') }}"
                        val="{{ $result?->first_name }}"
                        label="{{ __('language.first_name_label') }}" />
                </div>

                <div class="col-md-6">
                    <x-auth.input-field
                        type="text"
                        name="l_name"
                        id="l_name"
                        required="true"
                        place="{{ __('language.last_name_placeholder') }}"
                        val="{{ $result?->last_name }}"
                        label="{{ __('language.last_name_label') }}" />
                </div>

                <div class="col-md-6">
                    <x-auth.input-field
                        type="text"
                        name="phone_number"
                        id="phone_number"
                        required="true"
                        place="{{ __('language.phone_number_placeholder') }}"
                        val="{{ $result?->phone_number }}"
                        label="{{ __('language.phone_number_label') }}" />
                </div>

                <div class="col-md-6">
                    <x-auth.input-field
                        type="email"
                        name="email"
                        id="email"
                        required="true"
                        place="{{ __('language.email_placeholder') }}"
                        val="{{ $result?->email }}"
                        label="{{ __('language.email_label') }}" />
                </div>

                @if (! $isEdit)
                    <div class="col-md-6">
                        <x-role-list />
                    </div>
                @elseif ($currentRole)
                    <div class="col-md-6 form-floating">
                        <label class="form-label d-block" style='margin-top: -12px;'>{{ __('Role') }}</label>
                        <div class="form-control bg-light d-flex align-items-center">
                            {{ $currentRole->title ?? $currentRole->name }}
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-4 mx-auto mt-4">
                <div class="d-flex justify-content-center justify-content-md-end">
                    <x-auth.input-button
                        btn-class="btn btn-primary px-4 px-md-5"
                        btn-type="submit"
                        btn-value="{{ $isEdit ? __('language.edit') : __('language.create_new_staff_label') }}" />
                </div>
            </div>
        </x-auth.card>
    </div>

    <div class="col-12 col-xl-4">
        <x-auth.card card-header="{{ __('Profile Image') }}">
            <div class="d-flex flex-column align-items-center text-center gap-3">
                <x-auth.upload-file image="{{ $result?->profile() }}" />
                <p class="text-muted small mb-0">JPG, JPEG, PNG up to 2MB</p>
            </div>
        </x-auth.card>
    </div>

</x-auth.form>
