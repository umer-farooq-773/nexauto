<x-my-profile title="{{__('language.my_profile')}}" sub-title="{{__('language.my_profile_sub_title')}}">

    <x-auth.form form-action="{{ route('updatemyprofile') }}" enctype="true">
        @method('PUT')

            <div class="row">
                <div class="col-md-4 mx-auto border-0 shadow-none">
                    <div class="form-group">
                        <div class="py-4">
                            <x-auth.upload-file
                                image="{{ auth()->user()->profile()  }}"
                                name="file"
                                uid="profile_file"
                             />
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <x-auth.input-field type="text" name="f_name" id="f_name" required="true"
                        place="{{ __('language.first_name_placeholder') }}"
                        val="{{ auth()->user()->first_name }}" extraclasses="mb-3"
                        label="{{ __('language.first_name_label') }}" />
                </div>
                <div class="col-md-6">
                    <x-auth.input-field type="text" name="l_name" id="l_name" required="true"
                        place="{{ __('language.last_name_placeholder') }}" val="{{ auth()->user()->last_name }}"
                        extraclasses="mb-3" label="{{ __('language.last_name_label') }}" />
                </div>

                <div class="col-md-12">
                    <x-auth.input-field type="email" name="email" id="email" required="true"
                        place="{{ __('language.email_placeholder') }}" val="{{ auth()->user()->email }}"
                        extraclasses="mb-3 disabled" label="{{ __('language.email_label') }}" />
                </div>

                {{-- <div class="col-md-12">

                    <x-auth.text-area type="text" name="about" id="about" required="true"
                        place="{{ __('language.username_placeholder') }}" val="{{ auth()->user()->about }}"
                        extraclasses="mb-3" label="{{ __('language.biography_label') }}" />
                </div> --}}

                <div class="col-12 text-end">
                    <x-auth.input-button btn-class="btn btn-primary btn-modern px-4 shadow" btn-type="submit"
                        btn-value="{{ __('Update My Profile') }}" />
                </div>
            </div>
        
    </x-auth.form>

</x-my-profile>
