@php
    $count = $unreadCount;
@endphp

{{-- Notification Bell --}}
<div class="notification-wrapper position-relative" id="notificationDropdownWrapper">
    <button class="notification-bell-btn text-dark" id="notificationToggle" type="button" aria-label="Notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($count > 0)
            <span class="notification-badge">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div class="notification-dropdown" id="notificationDropdown">
        {{-- Header --}}
        <div class="notif-header">
            <div class="notif-header-left">
                <span class="notif-title">Notifications</span>
                @if($count > 0)
                    <span class="notif-count-pill">{{ $count }} new</span>
                @endif
            </div>
            @if($count > 0)
                <button type="button" class="notif-mark-all" id="markAllReadBtn"
                    data-url="{{ route('notifications.mark-all-read') }}"
                    data-token="{{ csrf_token() }}">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="notif-list">
            @forelse($notifications as $notification)
                @php
                    $isIncidentChat = isset($notification->data['type']) && $notification->data['type'] === 'incident_chat';
                    $targetUrl = $isIncidentChat ? ($notification->data['target_url'] ?? '#') : route('notifications.open', $notification->id);
                @endphp
                <a href="{{ $targetUrl }}" class="notif-item {{ $notification->read_at ? '' : 'unread' }}"
                   onclick="event.stopPropagation(); @if($isIncidentChat) markIncidentChatAsRead('{{ $notification->data['incident_type'] ?? '' }}', '{{ $notification->data['incident_id'] ?? '' }}'); @endif">
                    <div class="notif-icon-wrap">
                        <span class="notif-icon">
                            @if($isIncidentChat)
                                {{-- Chat icon for incident chat notifications --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                </svg>
                            @else
                                {{-- Default info icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            @endif
                        </span>
                    </div>
                    <div class="notif-content">
                        <p class="notif-message">{{ $notification->data['message'] ?? 'Notification' }}</p>
                        <span class="notif-time">
                            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if(!$notification->read_at)
                    <div class="notif-dot"></div>
                    @endif
                </a>
            @empty
                <div class="notif-empty">
                    <div class="notif-empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <p>You're all caught up!</p>
                    <small>No new notifications</small>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        {{-- Footer --}}
<div class="notif-footer">
    <a href="{{ route('notifications.all') }}" class="notif-view-all" id="viewAllNotifications">
        View all notifications
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
        </svg>
    </a>
</div>
    </div>
</div>

{{-- Hidden form outside the sticky nav, appended to body --}}
<form id="notif-mark-all-form" action="{{ route('notifications.mark-all-read') }}" method="POST"
      style="display:none; position:absolute;">
    @csrf
</form>

<style>
/* ── Bell Button ── */
.notification-wrapper {
    display: inline-flex;
    align-items: center;
}

.notification-bell-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px 10px;
    color: #555;
    border-radius: 10px;
    transition: background 0.2s, color 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-bell-btn:hover {
    background: rgba(0,0,0,0.06);
    color: #222;
}

.notification-bell-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
}

/* ── Badge ── */
.notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    line-height: 1;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(220, 53, 69, 0.5);
    animation: badgePop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes badgePop {
    0%   { transform: scale(0); }
    100% { transform: scale(1); }
}

/* ── Dropdown Panel ── */
.notification-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    width: 380px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.14), 0 2px 8px rgba(0,0,0,0.08);
    z-index: 9999;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.07);
    animation: dropdownSlide 0.2s ease;
}

.notification-dropdown.show {
    display: block;
 pointer-events: auto;
}

@keyframes dropdownSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Arrow pointer */
.notification-dropdown::before {
    content: '';
    position: absolute;
    top: -7px;
    right: 18px;
    width: 14px;
    height: 14px;
    background: #fff;
    border-left: 1px solid rgba(0,0,0,0.07);
    border-top: 1px solid rgba(0,0,0,0.07);
    transform: rotate(45deg);
    border-radius: 2px;
}

/* ── Header ── */
.notif-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px 12px;
    border-bottom: 1px solid #f0f0f0;
}

.notif-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.notif-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: -0.2px;
}

.notif-count-pill {
    background: #fff0f1;
    color: #dc3545;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 50px;
    border: 1px solid #ffd6d9;
}

.notif-mark-all {
    font-size: 12px;
    color: #6c757d;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
    white-space: nowrap;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-family: inherit;
    line-height: inherit;
}

.notif-mark-all:hover {
    color: #dc3545;
}

/* ── List ── */
.notif-list {
    max-height: 360px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #e0e0e0 transparent;
}

.notif-list::-webkit-scrollbar {
    width: 4px;
}

.notif-list::-webkit-scrollbar-track {
    background: transparent;
}

.notif-list::-webkit-scrollbar-thumb {
    background: #e0e0e0;
    border-radius: 4px;
}

/* ── Notification Item ── */
.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 20px;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f7f7f7;
    transition: background 0.15s;
    position: relative;
}

.notif-item:last-child {
    border-bottom: none;
}

.notif-item:hover {
    background: #fafafa;
    text-decoration: none;
    color: inherit;
}

.notif-item.unread {
    background: #fffbfb;
}

.notif-item.unread:hover {
    background: #fff5f5;
}

/* ── Icon ── */
.notif-icon-wrap {
    flex-shrink: 0;
    margin-top: 2px;
}

.notif-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #fff0f1, #ffe0e3);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc3545;
}

/* ── Content ── */
.notif-content {
    flex: 1;
    min-width: 0;
}

.notif-message {
    font-size: 13px;
    font-weight: 500;
    color: #2d2d2d;
    margin: 0 0 5px;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notif-time {
    font-size: 11px;
    color: #9e9e9e;
    display: flex;
    align-items: center;
    gap: 4px;
    font-weight: 400;
}

/* ── Unread dot ── */
.notif-dot {
    width: 8px;
    height: 8px;
    background: #dc3545;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 6px;
    box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
}

.notif-item:not(.unread) .notif-dot {
    display: none;
}

/* ── Empty State ── */
.notif-empty {
    padding: 40px 20px;
    text-align: center;
    color: #aaa;
}

.notif-empty-icon {
    width: 72px;
    height: 72px;
    background: #f5f5f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    color: #ccc;
}

.notif-empty p {
    font-size: 14px;
    font-weight: 600;
    color: #555;
    margin: 0 0 4px;
}

.notif-empty small {
    font-size: 12px;
    color: #aaa;
}

/* ── Footer ── */
.notif-footer {
    padding: 12px 20px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
}

.notif-view-all {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #dc3545;
    text-decoration: none;
    transition: gap 0.2s, opacity 0.2s;
}

.notif-view-all:hover {
    gap: 10px;
    opacity: 0.85;
    color: #dc3545;
    text-decoration: none;
}

/* ── Dark mode support ── */
body.dark-mode .notification-bell-btn {
    color: #ccc;
}

body.dark-mode .notification-bell-btn:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

body.dark-mode .notification-dropdown {
    background: #1e1e2e;
    border-color: rgba(255,255,255,0.08);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
}

body.dark-mode .notification-dropdown::before {
    background: #1e1e2e;
    border-color: rgba(255,255,255,0.08);
}

body.dark-mode .notif-header {
    border-color: rgba(255,255,255,0.07);
}

body.dark-mode .notif-title {
    color: #f0f0f0;
}

body.dark-mode .notif-item {
    border-color: rgba(255,255,255,0.05);
    color: #ddd;
}

body.dark-mode .notif-item.unread {
    background: rgba(220, 53, 69, 0.05);
}

body.dark-mode .notif-item:hover,
body.dark-mode .notif-item.unread:hover {
    background: rgba(255,255,255,0.05);
}

body.dark-mode .notif-message {
    color: #e0e0e0;
}

body.dark-mode .notif-footer {
    background: #181828;
    border-color: rgba(255,255,255,0.07);
}

body.dark-mode .notification-badge {
    border-color: #1e1e2e;
}

/* ── Responsive ── */
@media (max-width: 480px) {
    .notification-dropdown {
        width: calc(100vw - 20px);
        right: -60px;
    }
}
</style>

<script>
(function () {
    const toggle = document.getElementById('notificationToggle');
    const dropdown = document.getElementById('notificationDropdown');
    const markAllBtn = document.getElementById('markAllReadBtn');
    const viewAllLink = document.getElementById('viewAllNotifications');

    if (!toggle || !dropdown) return;

    // Toggle dropdown open/close
    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        dropdown.classList.toggle('show');
    });

    // Close when clicking outside
    document.addEventListener('click', function (e) {
        const wrapper = document.getElementById('notificationDropdownWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
        }
    });

    // Mark all read
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var form = document.getElementById('notif-mark-all-form');
            if (form) {
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // View all notifications - direct navigation without interference
    if (viewAllLink) {
        viewAllLink.addEventListener('click', function (e) {
            // Don't prevent default, just close dropdown and let navigation happen
            dropdown.classList.remove('show');
            // The browser will naturally navigate to href
        });
    }

    // Mark incident chat as read when notification is clicked
    window.markIncidentChatAsRead = function(incidentType, incidentId) {
        if (!incidentType || !incidentId) return;

        const baseUrl = '{{ route("incident_chat.mark_read", ["incidentType" => "TYPE", "incidentId" => "ID"]) }}';
        const url = baseUrl.replace('TYPE', incidentType).replace('ID', incidentId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the badge count
                const badge = document.getElementById('navbar-chat-badge');
                if (badge) {
                    const currentCount = parseInt(badge.textContent) || 0;
                    if (currentCount > 1) {
                        badge.textContent = currentCount - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }
        })
        .catch(error => console.error('Error marking incident chat as read:', error));
    };
})();
</script>
