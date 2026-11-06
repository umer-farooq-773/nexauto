<nav class="navbar sticky-top" style="z-index: 999 !important">
    <!-- Left Section -->
    <div class="navbar-left">
        <a href="#" class="sidebar-toggler">
            <i class="fas fa-bars"></i>
        </a>
        <span class="page-title">@yield('page-title', 'Dashboard')</span>
    </div>

    <!-- Right Section -->
    <div class="navbar-right">
        <!-- Dark Mode Toggle -->
        <button id="dark-mode-toggle" class="nav-action-btn mode-switch text-dark" aria-label="Toggle dark mode">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Chat Messages Badge -->
        {{-- <div class="chat-badge-wrapper position-relative" style="padding: 0 4px;">
            <a href="{{ route('notifications.all') }}"
               id="navbar-chat-btn"
               class="nav-action-btn text-dark"
               aria-label="Messages"
               style="display:inline-flex; text-decoration:none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span id="navbar-chat-badge"
                      style="display:none; position:absolute; top:-2px; right:-2px;
                             min-width:18px; height:18px; padding:0 4px;
                             background:#ef4444; color:#fff; font-size:10px; font-weight:700;
                             border-radius:50px; border:2px solid #fff;
                             box-shadow:0 2px 6px rgba(239,68,68,.35);
                             line-height:14px; text-align:center; pointer-events:none;">
                </span>
            </a>
        </div> --}}

        <!-- Notifications -->
        <div class=" notification-wrapper">
            <x-auth.notifications/>
        </div>

        <!-- User Profile -->
        <div class="user-dropdown-wrapper">
            <a href="#" class="user-trigger" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    <img
                        src="{{ auth()->user()?->profile() ?: asset('assets/img/user-img.png') }}"
                        onerror="this.onerror=null; this.src='{{ asset('assets/img/user-img.png') }}'"
                        alt="{{ auth()->user()?->full_name ?? __('User') }}"
                    >
                    <span class="status-dot"></span>
                </div>
                <span class="user-name d-none d-lg-inline">{{ auth()->user()?->full_name ?? __('Guest') }}</span>
                <i class="fas fa-chevron-down user-chevron"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-end">
                <div class="dropdown-header">
                    <div class="user-info">
                        <div class="user-avatar-sm">
                            <img
                                src="{{ auth()->user()?->profile() ?: asset('assets/img/user-img.png') }}"
                                onerror="this.onerror=null; this.src='{{ asset('assets/img/user-img.png') }}'"
                                alt="{{ auth()->user()?->full_name ?? __('User') }}"
                            >
                        </div>
                        <div class="user-details">
                            <span class="user-fullname">{{ auth()->user()?->full_name ?? __('Guest') }}</span>
                            <span class="user-email">{{ auth()->user()?->email ?? '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <a href="{{ route('myprofile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i>
                    <span>My Profile</span>
                </a>

                <a href="{{ route('change_password') }}" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>

                @can('edit_vehicle')
                    <form action="{{ route('wialon.sync_vehicle_settings') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sync-alt"></i>
                            <span>Sync Wialon Data</span>
                        </button>
                    </form>
                @endcan

                <div class="dropdown-divider"></div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>

                <button class="dropdown-item logout-item"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<style>
    /* ===== NAVBAR CONTAINER ===== */
.navbar {
    background: #ffffff;
    border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    padding: 0 9px;
    height: 83px; /* Match sidebar brand height */
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 1000 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
    transition: box-shadow 0.3s ease, background 0.3s ease;
}

/* Enhanced shadow on scroll */
.navbar.scrolled {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06), 0 1px 4px rgba(0, 0, 0, 0.04);
}

/* ===== LEFT SECTION ===== */
.navbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Sidebar Toggle Button - Enhanced */
.sidebar-toggler {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    color: #4a4a4a;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.sidebar-toggler:hover {
    background: #f5f7fa;
    color: #0066ff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 102, 255, 0.08);
    transform: translateY(-1px);
}

.sidebar-toggler:active {
    transform: scale(0.95);
}

/* Page Title */
.page-title {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    letter-spacing: -0.3px;
    position: relative;
}

/* ===== RIGHT SECTION ===== */
.navbar-right {
    display: flex;
    align-items: center;
    gap: 2px;
}

/* ===== ACTION BUTTONS (Dark Mode & Notifications) ===== */
.nav-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #4a4a4a;
    font-size: 16px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    padding: 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.nav-action-btn:hover {
    background: #f5f7fa;
    color: #0066ff;
    box-shadow: 0 2px 8px rgba(0, 102, 255, 0.08);
    transform: translateY(-1px);
}

.nav-action-btn:active {
    transform: scale(0.92);
}

/* Notification Wrapper - Enhanced */
.notification-wrapper {
    width: auto;
    padding: 0 4px;
}

.notification-wrapper:hover {
    background: transparent;
}

/* Notification Badge - Enhanced */
.notification-wrapper .badge {
    position: absolute;
    top: -2px;
    right: -2px;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 50px;
    background: #ef4444;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
    border: 2px solid #ffffff;
}

/* ===== USER DROPDOWN ===== */
.user-dropdown-wrapper {
    position: relative;
    margin-left: 4px;
}

.user-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 4px 12px 4px 4px;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    cursor: pointer;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.user-trigger:hover {
    background: #f5f7fa;
    border-color: rgba(0, 0, 0, 0.06);
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transform: translateY(-1px);
}

.user-trigger:active {
    transform: scale(0.98);
}

/* User Avatar - Enhanced */
.user-avatar {
    position: relative;
    flex-shrink: 0;
}

.user-avatar img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
}

.user-trigger:hover .user-avatar img {
    border-color: #0066ff;
    box-shadow: 0 4px 12px rgba(0, 102, 255, 0.15);
}

/* Status Dot - Enhanced */
.status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 6px rgba(34, 197, 94, 0.3);
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0);
    }
}

/* User Name */
.user-name {
    font-size: 13.5px;
    font-weight: 500;
    color: #1a1a1a;
    letter-spacing: -0.2px;
}

/* Chevron Icon */
.user-chevron {
    font-size: 11px;
    color: #94a3b8;
    transition: transform 0.3s ease;
}

.user-trigger[aria-expanded="true"] .user-chevron {
    transform: rotate(180deg);
}

/* ===== DROPDOWN MENU - Enhanced ===== */
.dropdown-menu {
    border: 1px solid rgba(0, 0, 0, 0.04);
    border-radius: 12px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 8px;
    min-width: 250px;
    margin-top: 12px;
    background: #ffffff;
    animation: dropdownFade 0.25s ease;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.98);
}

@keyframes dropdownFade {
    from {
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Dropdown Arrow */
.dropdown-menu::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 18px;
    width: 12px;
    height: 12px;
    background: rgba(255, 255, 255, 0.98);
    border-top: 1px solid rgba(0, 0, 0, 0.04);
    border-left: 1px solid rgba(0, 0, 0, 0.04);
    transform: rotate(45deg);
}

/* Dropdown Header */
.dropdown-header {
    padding: 8px 12px 12px 12px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-sm {
    flex-shrink: 0;
}

.user-avatar-sm img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(0, 0, 0, 0.04);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.user-fullname {
    font-size: 13.5px;
    font-weight: 600;
    color: #000000;
}

.user-email {
    font-size: 12px;
    color: #8892a0;
}

/* Dropdown Items - Enhanced */
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 400;
    color: #4a4a4a;
    transition: all 0.2s ease;
    text-decoration: none;
    position: relative;
}

.dropdown-item:hover {
    background: #f5f7fa;
    color: #1a1a1a;
    text-decoration: none;
    transform: translateX(2px);
}

.dropdown-item i {
    width: 16px;
    font-size: 14px;
    color: #94a3b8;
    transition: all 0.2s ease;
    text-align: center;
}

.dropdown-item:hover i {
    color: #0066ff;
    transform: scale(1.1);
}

/* Dropdown Divider */
.dropdown-divider {
    margin: 6px 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.04);
}

/* Logout Item - Enhanced */
.logout-item {
    color: #e74c3c;
}

.logout-item i {
    color: #e74c3c;
}

.logout-item:hover {
    background: #fef2f2;
    color: #dc3545;
    transform: translateX(2px);
}

.logout-item:hover i {
    color: #dc3545;
    transform: scale(1.1);
}

.logout-item kbd {
    margin-left: auto;
    padding: 2px 8px;
    background: #f0f0f0;
    border-radius: 4px;
    font-size: 10px;
    color: #94a3b8;
    font-weight: 500;
    font-family: inherit;
    border: 1px solid #e8ecf0;
}

.logout-item:hover kbd {
    background: #e8e8e8;
    border-color: #ddd;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .navbar {
        padding: 0 16px;
        height: 60px;
        border-top: 1px solid #f0f0f0;
        box-shadow: 0 222px 12px rgba(0, 0, 0, 0.04);
    }

    .page-title {
        font-size: 14px;
    }

    .sidebar-toggler {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }

    .user-avatar img {
        width: 30px;
        height: 30px;
    }

    .user-trigger {
        padding: 2px 8px 2px 2px;
    }

    .nav-action-btn {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }

    .dropdown-menu {
        min-width: 220px;
        right: -10px;
    }

    .user-name {
        display: none;
    }
}

/* ===== DARK MODE SUPPORT (Matches Sidebar) ===== */
@media (prefers-color-scheme: dark) {
    .navbar {
        background: #ffffff;
        border-bottom: 1px solid #f0f0f0;z
        box-shadow: 0 1px 3px rgba(255, 255, 255, 0.02);
    }

    .navbar.scrolled {
        box-shadow: 0 4px 20px rgba(255, 255, 255, 0.04);
    }

    .page-title {
        color: #000000;
    }

    .sidebar-toggler {
        color: #94a3b8;
        box-shadow: 0 1px 3px rgba(255, 255, 255, 0.02);
    }

    .sidebar-toggler:hover {
        background: rgba(255, 255, 255, 0.04);
        color: #4d8eff;
        box-shadow: 0 2px 8px rgba(77, 142, 255, 0.08);
    }

    .user-name {
        color: #000000;
    }

    .user-trigger:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: #2a2a2a;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .user-avatar img {
        border-color: #1a1a1a;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .status-dot {
        border-color: #1a1a1a;
        box-shadow: 0 2px 6px rgba(34, 197, 94, 0.2);
    }

    .user-chevron {
        color: #000000;
    }

    .nav-action-btn {
        color: #94a3b8;
        box-shadow: 0 1px 3px rgba(255, 255, 255, 0.02);
    }

    .nav-action-btn:hover {
        background: rgba(255, 255, 255, 0.04);
        color: #4d8eff;
        box-shadow: 0 2px 8px rgba(77, 142, 255, 0.08);
    }

    .dropdown-menu {
        background: rgba(255, 255, 255, 0.98);
        border-color: rgba(255, 255, 255, 0.04);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
    }

    .dropdown-menu::before {
        background: rgba(255, 255, 255, 0.98);
        border-top-color: rgba(255, 255, 255, 0.04);
        border-left-color: rgba(255, 255, 255, 0.04);
    }

    .user-fullname {
        color: #000000;
    }

    .user-email {
        color: #8892a0;
    }

    .user-avatar-sm img {
        border-color: #2a2a2a;
    }

    .dropdown-item {
        color: #94a3b8;
    }

    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.04);
        color: #4d8eff;
    }

    .dropdown-item i {
        color: #64748b;
    }

    .dropdown-item:hover i {
        color: #4d8eff;
    }

    .dropdown-divider {
        border-top-color: #2a2a2a;
    }

    .logout-item kbd {
        background: #2a2a2a;
        color: #64748b;
    }

    .logout-item:hover kbd {
        background: #333;
        border-color: #444;
    }
}

/* ===== SCROLL ANIMATION SCRIPT ===== */
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.querySelector('.navbar');
        let lastScroll = 0;

        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

            if (currentScroll > 10) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });

        // Update incident chat unread count badge
        function updateIncidentChatBadge() {
            fetch('{{ route("incident_chat.unread_count") }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('navbar-chat-badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching incident chat count:', error));
        }

        // Initial load and periodic updates
        updateIncidentChatBadge();
        setInterval(updateIncidentChatBadge, 30000); // Update every 30 seconds
    });
</script>
</style>
