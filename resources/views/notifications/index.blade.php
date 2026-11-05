<x-layouts.auth page-title="All Notifications">
    <div class="container-fluid pt-4 px-4">
        <div class="row">
            <div class="col-12">
                <div class="all-notif-card">
                    <div class="all-notif-header">
                        <div>
                            <h5 class="all-notif-title">All Notifications</h5>
                            <p class="all-notif-subtitle">Your complete notification history</p>
                        </div>
                        @if($notifications->where('read_at', null)->count() > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-mark-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Mark all as read
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(session('success'))
                        <div class="notif-alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="all-notif-list">
                        @forelse($notifications as $notification)
                            <a href="{{ route('notifications.open', $notification->id) }}"
                               class="all-notif-item {{ is_null($notification->read_at) ? 'unread' : 'read' }}">
                                <div class="all-notif-icon {{ is_null($notification->read_at) ? 'icon-unread' : 'icon-read' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                </div>
                                <div class="all-notif-body">
                                    <p class="all-notif-msg">{{ $notification->data['message'] }}</p>
                                    <span class="all-notif-time">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        {{ $notification->created_at->diffForHumans() }}
                                        &nbsp;·&nbsp;
                                        {{ $notification->created_at->format('M d, Y \a\t h:i A') }}
                                    </span>
                                </div>
                                <div class="all-notif-status">
                                    @if(is_null($notification->read_at))
                                        <span class="status-badge unread-badge">New</span>
                                    @else
                                        <span class="status-badge read-badge">Read</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="all-notif-empty">
                                <div class="empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                    </svg>
                                </div>
                                <h6>No notifications yet</h6>
                                <p>When you receive notifications, they'll appear here.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($notifications->hasPages())
                        <div class="all-notif-pagination">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
    .all-notif-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.07);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .all-notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 28px 20px;
        border-bottom: 1px solid #f0f0f0;
    }

    .all-notif-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
        margin: 0 0 4px;
    }

    .all-notif-subtitle {
        font-size: 13px;
        color: #9e9e9e;
        margin: 0;
    }

    .btn-mark-all {
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

    .btn-mark-all:hover {
        background: #ffe0e3;
        border-color: #dc3545;
    }

    .notif-alert-success {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 16px 28px 0;
        padding: 12px 16px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        color: #16a34a;
        font-size: 13px;
        font-weight: 500;
    }

    .all-notif-list {
        padding: 8px 0;
    }

    .all-notif-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 28px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #f7f7f7;
        transition: background 0.15s;
    }

    .all-notif-item:last-child {
        border-bottom: none;
    }

    .all-notif-item:hover {
        background: #fafafa;
        text-decoration: none;
        color: inherit;
    }

    .all-notif-item.unread {
        background: #fffbfb;
    }

    .all-notif-item.unread:hover {
        background: #fff5f5;
    }

    .all-notif-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .icon-unread {
        background: linear-gradient(135deg, #fff0f1, #ffe0e3);
        color: #dc3545;
    }

    .icon-read {
        background: #f5f5f5;
        color: #aaa;
    }

    .all-notif-body {
        flex: 1;
        min-width: 0;
    }

    .all-notif-msg {
        font-size: 14px;
        font-weight: 500;
        color: #2d2d2d;
        margin: 0 0 6px;
        line-height: 1.5;
    }

    .all-notif-item.read .all-notif-msg {
        color: #777;
        font-weight: 400;
    }

    .all-notif-time {
        font-size: 12px;
        color: #9e9e9e;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .all-notif-status {
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .status-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 50px;
    }

    .unread-badge {
        background: #fff0f1;
        color: #dc3545;
        border: 1px solid #ffd6d9;
    }

    .read-badge {
        background: #f5f5f5;
        color: #aaa;
        border: 1px solid #e8e8e8;
    }

    .all-notif-empty {
        padding: 60px 20px;
        text-align: center;
        color: #aaa;
    }

    .empty-icon {
        width: 88px;
        height: 88px;
        background: #f5f5f5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        color: #ccc;
    }

    .all-notif-empty h6 {
        font-size: 16px;
        font-weight: 600;
        color: #555;
        margin: 0 0 6px;
    }

    .all-notif-empty p {
        font-size: 13px;
        color: #aaa;
        margin: 0;
    }

    .all-notif-pagination {
        padding: 16px 28px;
        border-top: 1px solid #f0f0f0;
    }
    </style>
</x-layouts.auth>
