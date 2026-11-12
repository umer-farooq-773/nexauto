<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<div class="sidebar">
    <!-- Brand/Logo Section -->
    <div class="sidebar-brand">
        <div class="brand-wrapper">
            <div class="brand-icon">
                <x-logo/>
            </div>
            <div class="brand-info">
                <span class="brand-name">{{ config('app.name') }}</span>
                <span class="brand-subtitle">Administration</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        @foreach ($sidebarmenus as $menu)
            @php
                $hasChildren = $menu->children && $menu->children->count();
                $routeName = request()->route()?->getName() ?? '';
                $isActive = $menu->route ? Str::startsWith($routeName, $menu->route) : false;
                $uniqueId = 'menuDropdown_' . $menu->id;

                $menuPermissions = $menu->permissions->pluck('name')->toArray();
                $canViewMenu = empty($menuPermissions) || auth()->user()->hasAnyPermission($menuPermissions);

                $isChildActive = false;
                if ($hasChildren) {
                    foreach ($menu->children as $child) {
                        if ($child->route && Str::startsWith($routeName, $child->route)) {
                            $isChildActive = true;
                            break;
                        }
                    }
                }
            @endphp

            @if($canViewMenu)
                @if ($hasChildren)
                    <!-- Menu with Children -->
                    <div class="nav-group">
                        <button
                            class="nav-group-toggle {{ $isChildActive ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $uniqueId }}"
                            aria-expanded="{{ $isChildActive ? 'true' : 'false' }}"
                            aria-controls="{{ $uniqueId }}"
                        >
                            <span class="nav-icon-wrapper">
                                <i class="fas {{ $menu->icon }} nav-icon"></i>
                            </span>
                            <span class="nav-label">{{ __($menu->name) }}</span>
                            <span class="nav-chevron {{ $isChildActive ? 'rotated' : '' }}">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>

                        <div class="collapse {{ $isChildActive ? 'show' : '' }}" id="{{ $uniqueId }}">
                            <div class="nav-group-items">
                                @foreach ($menu->children as $child)
                                    @php
                                        $childPermissions = $child->permissions->pluck('name')->toArray();
                                        $canViewChild = empty($childPermissions) || auth()->user()->hasAnyPermission($childPermissions);
                                    @endphp

                                    @if($canViewChild)
                                        @php
                                            $isChildItemActive = $child->route ? Str::startsWith($routeName, $child->route) : false;
                                        @endphp
                                        <a
                                            href="{{ $child->route ? route($child->route, $child->route_param) : '#' }}"
                                            class="nav-sub-item {{ $isChildItemActive ? 'active' : '' }}"
                                        >
                                            <span class="sub-item-bullet"></span>
                                            <span class="sub-item-text">{{ __($child->name) }}</span>
                                            @if($isChildItemActive)
                                                <span class="sub-item-active-bar"></span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Single Menu Item -->
                    <a
                        class="nav-item {{ $isActive ? 'active' : '' }}"
                        href="{{ $menu->route ? route($menu->route, $menu->route_param) : '#' }}"
                    >
                        <span class="nav-icon-wrapper">
                            <i class="fas {{ $menu->icon }} nav-icon"></i>
                        </span>
                        <span class="nav-label">{{ __($menu->name) }}</span>
                        @if($isActive)
                            <span class="nav-active-indicator"></span>
                        @endif
                    </a>
                @endif
            @endif
        @endforeach

        <!-- Vehicle Telemetry / History -->
        <a
            class="nav-item {{ request()->routeIs('vehicle_history.*') ? 'active' : '' }}"
            href="{{ route('vehicle_history.index') }}"
        >
            <span class="nav-icon-wrapper">
                <i class="fas fa-gauge-high nav-icon"></i>
            </span>
            <span class="nav-label">{{ __('Vehicle Telemetry') }}</span>
            @if(request()->routeIs('vehicle_history.*'))
                <span class="nav-active-indicator"></span>
            @endif
        </a>

        <!-- Track Incidents Menu -->
        <a
            class="nav-item {{ request()->routeIs('incident.track*') ? 'active' : '' }}"
            href="{{ route('incident.track') }}"
        >
            <span class="nav-icon-wrapper">
                <i class="fas fa-location-crosshairs nav-icon"></i>
            </span>
            <span class="nav-label">{{ __('Track Incidents') }}</span>
            @if(request()->routeIs('incident.track*'))
                <span class="nav-active-indicator"></span>
            @endif
        </a>

        <!-- Map Button - Admin Only -->
        @if(auth()->check() && auth()->user()->hasRole('admin'))
    <a
        class="nav-item {{ request()->routeIs('map.view') ? 'active' : '' }}"
        href="{{ route('map.view') }}"
    >
        <span class="nav-icon-wrapper">
    <i class="fas fa-satellite-dish nav-icon"></i>
</span>
<span class="nav-label">Monitoring</span>
        @if(request()->routeIs('map.view'))
            <span class="nav-active-indicator"></span>
        @endif
    </a>
@endif

        <!-- Notifications (Conditional) -->
        @if(auth()->check() && auth()->user()->hasRole(\App\Constants\Constants::CLIENT_DIRECTORS))
            @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
            <a
                class="nav-item {{ request()->routeIs('notifications.all') ? 'active' : '' }}"
                href="{{ route('notifications.all') }}"
            >
                <span class="nav-icon-wrapper">
                    <i class="fas fa-bell nav-icon"></i>
                </span>
                <span class="nav-label">Action Responded</span>
                @if($unreadCount > 0)
                    <span class="notification-badge">{{ $unreadCount }}</span>
                @endif
                @if(request()->routeIs('notifications.all'))
                    <span class="nav-active-indicator"></span>
                @endif
            </a>
        @endif
    </nav>

    <!-- Footer with Logout -->
    <div class="sidebar-footer">
        <div class="footer-divider"></div>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
        <button
            class="logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
        >
            <span class="logout-icon-wrapper">
                <i class="fas fa-sign-out-alt logout-icon"></i>
            </span>
            <span class="logout-label">Sign Out</span>
        </button>
    </div>
</div>

<style>
    /* ===== SIDEBAR CONTAINER ===== */
.sidebar {
    width: 260px;
    height: 100vh;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    border-right: 1px solid #f0f0f0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* ===== BRAND SECTION ===== */
.sidebar-brand {
    padding: 9px 29px 19px;
    border-bottom: 1px solid #f0f0f0;
    flex-shrink: 0;
}

.brand-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 15px;
}

.brand-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    /* background: #f0f4ff; */
    border-radius: 10px;
    flex-shrink: 0;
}

.brand-icon svg {
    width: 24px;
    height: 24px;
}

.brand-info {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.brand-name {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    letter-spacing: -0.3px;
    line-height: 1.2;
}

.brand-subtitle {
    font-size: 10px;
    font-weight: 500;
    color: #8892a0;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* ===== NAVIGATION CONTAINER ===== */
.sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* ===== SINGLE NAVIGATION ITEM ===== */
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 8px;
    color: #4a4a4a;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
    position: relative;
    background: transparent;
    border: none;
    width: 100%;
}

.nav-item:hover {
    background: #f5f7fa;
    color: #1a1a1a;
    text-decoration: none;
}

.nav-item.active {
    background: #f0f4ff;
    color: #0066ff;
    font-weight: 600;
}

.nav-item .nav-icon-wrapper {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 8px;
    background: transparent;
    transition: all 0.15s ease;
}

.nav-item:hover .nav-icon-wrapper {
    background: #edf2f7;
}

.nav-item.active .nav-icon-wrapper {
    background: #dbeafe;
}

.nav-item .nav-icon {
    font-size: 16px;
    color: inherit;
}

.nav-item .nav-label {
    flex: 1;
    white-space: nowrap;
}

/* Active Indicator - Right side bar */
.nav-active-indicator {
    width: 3px;
    height: 24px;
    background: #0066ff;
    border-radius: 4px;
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        height: 0;
        opacity: 0;
    }
    to {
        height: 24px;
        opacity: 1;
    }
}

/* ===== NOTIFICATION BADGE ===== */
.notification-badge {
    background: #ff3b30;
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 1px 8px;
    border-radius: 10px;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* ===== GROUP TOGGLE (Parent Menu) ===== */
.nav-group {
    display: flex;
    flex-direction: column;
}

.nav-group-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 8px;
    color: #4a4a4a;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
}

.nav-group-toggle:hover {
    background: #f5f7fa;
    color: #1a1a1a;
}

.nav-group-toggle.active {
    background: #f0f4ff;
    color: #0066ff;
    font-weight: 600;
}

.nav-group-toggle .nav-icon-wrapper {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 8px;
    background: transparent;
    transition: all 0.15s ease;
}

.nav-group-toggle:hover .nav-icon-wrapper {
    background: #edf2f7;
}

.nav-group-toggle.active .nav-icon-wrapper {
    background: #dbeafe;
}

.nav-group-toggle .nav-icon {
    font-size: 16px;
    color: inherit;
}

.nav-group-toggle .nav-label {
    flex: 1;
    white-space: nowrap;
}

.nav-group-toggle .nav-chevron {
    margin-left: auto;
    font-size: 12px;
    transition: transform 0.3s ease;
    color: #999;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-group-toggle .nav-chevron.rotated {
    transform: rotate(180deg);
}

/* ===== SUB-MENU ITEMS - ENHANCED WITH BULLETS & BARS ===== */
.nav-group-items {
    padding: 4px 0 4px 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nav-sub-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 7px 12px 7px 48px;
    border-radius: 6px;
    color: #4a4a4a;
    text-decoration: none;
    font-size: 13px;
    font-weight: 400;
    transition: all 0.15s ease;
    position: relative;
}

.nav-sub-item:hover {
    background: #f5f7fa;
    color: #1a1a1a;
    text-decoration: none;
    transform: translateX(3px);
}

.nav-sub-item.active {
    color: #0066ff;
    font-weight: 500;
    background: #f0f4ff;
}

/* Sub-item bullet (dot) */
.nav-sub-item .sub-item-bullet {
    width: 6px;
    height: 6px;
    background: #cbd5e1;
    border-radius: 50%;
    flex-shrink: 0;
    transition: all 0.25s ease;
    position: relative;
}

.nav-sub-item:hover .sub-item-bullet {
    background: #94a3b8;
    transform: scale(1.2);
}

.nav-sub-item.active .sub-item-bullet {
    background: #0066ff;
    box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
    transform: scale(1.1);
}

/* Sub-item text */
.nav-sub-item .sub-item-text {
    flex: 1;
    letter-spacing: -0.1px;
}

/* Active bar on the right side of sub-item */
.nav-sub-item .sub-item-active-bar {
    width: 3px;
    height: 20px;
    background: #0066ff;
    border-radius: 4px;
    flex-shrink: 0;
    animation: slideIn 0.3s ease;
}

/* ===== FOOTER ===== */
.sidebar-footer {
    padding: 9px 14px 7px;
    border-top: 1px solid #f0f0f0;
    flex-shrink: 0;
    margin-top: auto;
}

.footer-divider {
    display: none;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 8px;
    color: #4a4a4a;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
}

.logout-btn:hover {
    background: #fef2f2;
    color: #dc3545;
}

.logout-btn .logout-icon-wrapper {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 8px;
    background: transparent;
    transition: all 0.15s ease;
}

.logout-btn:hover .logout-icon-wrapper {
    background: #fecaca;
}

.logout-btn .logout-icon {
    font-size: 16px;
    color: inherit;
}

.logout-btn .logout-label {
    flex: 1;
    white-space: nowrap;
}

/* ===== SCROLLBAR ===== */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #e0e0e0;
    border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #d0d0d0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        border-right: none;
        border-bottom: 1px solid #f0f0f0;
    }

    .sidebar-nav {
        padding: 12px 8px;
        max-height: 60vh;
    }
}
</style>
