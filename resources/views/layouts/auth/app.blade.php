@include('layouts.auth.links')

<x-auth.sidebar />

<div class="content">
    @include('layouts.auth.header')

    <style>
        .clear-all {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff0f1;
            color: #dc3545;
            border: 1px solid #ffd6d9;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s;
        }

        .clear-all:hover {
            background: #ffe0e3;
            border-color: #dc3545;
        }
    </style>

    <div class="container-fluid px-4">
        <div class="mt-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="tracking-tight">{{ $pageTitle ?? 'Dashboard' }}</h5>
                    <p class="text-muted">{{ $subTitle }}</p>
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-end mb-3 mb-md-0">
                        {{-- Clear All Button - ONLY on notifications page --}}
                        @if(Route::currentRouteName() === 'notifications.all')
                            <form action="{{ route('notifications.clear-all') }}" method="POST" class="d-inline me-2">
                                @csrf
                                <button type="submit" class="clear-all">
                                    Clear All
                                </button>
                            </form>
                        @endif

                        @isset($actionButton)
                            {{ $actionButton }}
                        @else
                            @if (request()->routeIs('auth'))
                                {{-- Your auth filter code here --}}
                            @endif
                        @endisset
                    </div>
                </div>
            </div>

            @isset($quickLinks)
                <div class="row g-3 mb-4">
                    {{ $quickLinks }}
                </div>
            @endisset
        </div>

        {{ $slot }}
    </div>

    @include('layouts.auth.footer')
</div>

@include('layouts.auth.scripts')
