<x-layouts.auth page-title="{{ $title }}" sub-title="{{ $subTitle }}">

    @push('auth_styles')
    <style>
        /* Tabs ke beech smooth transition */
        .custom-tab-item {
            transition: all 0.3s ease;
            border: 1px solid transparent;
            color: #6c757d;
        }

        /* Active Tab Glow */
        .nav-pills .nav-link.active.custom-tab-item {

            background-color: #0b3d91 !important;
            color: #fff !important;
        }

        .nav-pills .nav-link.custom-tab-item:hover {

            color: #0b3d91;
        }

        .nav-item{
            width: auto !important;
        }

        /* Scrollbar hide karne ke liye classes */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE/Edge */
            scrollbar-width: none;  /* Firefox */
            scroll-behavior: smooth;
        }

        /* Mobile optimization */
        @media (max-width: 768px) {
            .custom-tab-item {
            font-size: 13px;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }
    </style>
    @endpush

<x-slot name="pageTitle">{{ $title }}</x-slot>
<div class="row g-4 mb-3">
    <div class="col-md-4">
        <div class="card-custom text-center bg-light py-4 overflow-hidden">
            <div class="profile-header-cover"></div>
            <div class="d-flex justify-content-center">
                <div class="profile-img-container position-relative d-inline-block">
                    <img
                        src="{{ auth()->user()->profile() }}"
                        class="rounded border shadow-sm"
                        style="width:120px;height:100px;object-fit:cover;"
                        onerror="this.onerror=null; this.src='{{ asset('assets/img/user-img.png') }}'"
                        {{-- class=" border shadow-sm"
                        style="width:120px;height:120px;object-fit:cover;border-radius:50%" --}}
                        alt="{{ auth()->user()->full_name }}">
                </div>
            </div>
            <h4 class="mt-3 fw-bold">{{auth()->user()->full_name}}</h4>
            <p class="text-muted">{{auth()->user()->roles[0]->name}}</p>
            <div class="px-4">
                <hr>
                <div class="d-flex justify-content-between text-start mb-2">
                    <span class="text-muted small">Status</span>
                    <span class="badge bg-success-subtle text-success">Active</span>
                </div>
                <div class="d-flex justify-content-between text-start mb-4">
                    <span class="text-muted small">Joined</span>
                    <span class="fw-600 small">{{auth()->user()->created_at->format('M Y')}}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom border-0 overflow-hidden">
            <div class="p-3 bg-light border-bottom">
                <ul class="nav nav-pills nav-fill gap-2 p-1 rounded-pill  overflow-x-auto hide-scrollbar"
                    id="advancedProfileTabs" role="tablist" style="white-space: nowrap;">

                    <li class="nav-item" role="presentation">
                        <a class="rounded-pill fw-bold text-nowrap d-flex align-items-center justify-content-center px-4 py-2 custom-tab-item {{ request()->route()->getName() == 'myprofile' ? 'nav-link active' : '' }}"
                            href="{{ route('myprofile') }}">
                            <i class="align-middle me-1 fas fa-fw fa-id-card"></i> {{__('language.account')}}
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="rounded-pill fw-bold text-nowrap d-flex align-items-center justify-content-center px-4 py-2 custom-tab-item {{ request()->route()->getName() == 'change_password' ? 'nav-link active' : '' }}"
                            href="{{ route('change_password') }}">
                            <i class="align-middle me-1 fas fa-fw fa-lock"></i> {{__('language.change_password')}}
                        </a>
                    </li>

                    <li class="nav-item" role="presentation">
                        <a class="rounded-pill fw-bold text-nowrap d-flex align-items-center justify-content-center px-4 py-2 custom-tab-item {{ request()->route()->getName() == 'safety_privacy' ? 'nav-link active' : '' }}"
                            href="{{ route('safety_privacy') }}">
                            <i class="align-middle me-1 fas fa-fw fa-shield-alt"></i> {{__('language.safety_privacy')}}
                        </a>
                    </li>
                </ul>
            </div>

            <x-auth.card card-body="card-body bg-light">
                {{ $slot }}
            </x-auth.card>
        </div>
    </div>
</div>
</x-layouts.auth>
