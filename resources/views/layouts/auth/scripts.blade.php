<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/lib/chart/chart.min.js') }}"></script>
<script src="{{ asset('assets/lib/easing/easing.min.js') }}"></script>
<script src="{{ asset('assets/lib/waypoints/waypoints.min.js') }}"></script>
<script src="{{ asset('assets/lib/owlcarousel/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
<script src="{{ asset('assets/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>

<!-- Template Javascript -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Google Maps API (for address autocomplete) -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_API_KEY&libraries=places"></script>

<script>
    function showToaster(type, message, title) {
        toastr.options.closeButton = true;
        toastr.options.showMethod = 'slideDown';
        toastr.options.closeEasing = 'swing';
        toastr.options.progressBar = true;
        toastr.options.timeOut = 2500;
        toastr.options.positionClass = "toast-bottom-right";

        toastr[type](message, title);
    }

    @if (session('warning'))
        showToaster('warning', '{{ session('warning') }}', 'Warning');
    @elseif (session('success'))
        showToaster('success', '{{ session('success') }}', 'Success');
    @elseif (session('error'))
        showToaster('error', '{{ session('error') }}', 'Error');
    @elseif (session('info'))
        showToaster('info', '{{ session('info') }}', 'Info');
    @endif
</script>

@stack('auth_scripts')
@stack('scripts')

<script>
    document.querySelectorAll('.sidebar-toggle').forEach(function(toggle) {
        var targetId = toggle.getAttribute('href').replace('#', '');
        var collapseEl = document.getElementById(targetId);
        if (!collapseEl) return;

        collapseEl.addEventListener('show.bs.collapse', function() {
            toggle.querySelector('.toggle-icon').style.transform = 'rotate(180deg)';
        });
        collapseEl.addEventListener('hide.bs.collapse', function() {
            toggle.querySelector('.toggle-icon').style.transform = 'rotate(0deg)';
        });
    });
</script>

{{-- ======================================================
     Chat Notification Badge — real-time unread message count
     Works on every page. Reuses the Pusher SDK that Chatify
     already loads on the chat page. On non-chat pages we load
     Pusher ourselves (deferred, only if not already present).
     ====================================================== --}}
<script>
(function () {
    'use strict';

    // ── Config injected from Laravel ────────────────────────────────────────
    var CHAT_BADGE_CONFIG = {
        authId       : '{{ auth()->id() }}',
        unreadUrl    : '{{ route("chat.unread_count") }}',
        chatUrl      : '{{ config("chatify.routes.prefix") }}',
        csrfToken    : '{{ csrf_token() }}',
        pusherKey    : '{{ config("chatify.pusher.key") }}',
        pusherCluster: '{{ config("chatify.pusher.options.cluster") }}',
        pusherHost   : '{{ config("chatify.pusher.options.host") }}',
        pusherPort   : {{ config("chatify.pusher.options.port", 443) }},
        pusherTLS    : {{ config("chatify.pusher.options.useTLS") ? 'true' : 'false' }},
        pusherAuthUrl: '{{ route("pusher.auth") }}'
    };

    var badge      = document.getElementById('navbar-chat-badge');
    var chatBtn    = document.getElementById('navbar-chat-btn');
    var badgeCount = 0;

    // Detect if the user is currently viewing a Chatify conversation
    function isOnChatPage() {
        return window.location.pathname.indexOf(
            CHAT_BADGE_CONFIG.chatUrl.replace(/^\//, '')
        ) !== -1;
    }

    // Get the currently open conversation partner id (Chatify sets this meta tag)
    function getOpenConversationId() {
        var meta = document.querySelector('meta[name="id"]');
        return meta ? meta.getAttribute('content') : null;
    }

    // ── Badge rendering ─────────────────────────────────────────────────────
    function renderBadge(count) {
        badgeCount = count;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    // ── Fetch unread count from server ───────────────────────────────────────
    function fetchUnreadCount() {
        fetch(CHAT_BADGE_CONFIG.unreadUrl, {
            headers: {
                'X-CSRF-TOKEN': CHAT_BADGE_CONFIG.csrfToken,
                'Accept'      : 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderBadge(data.count || 0); })
        .catch(function () { /* silently ignore */ });
    }

    // ── Increment badge when a new message arrives ───────────────────────────
    function onNewMessage(data) {
        // Only increment if:
        // 1. Message is FOR the current user (to_id == authId)
        // 2. Current user is NOT already viewing that conversation
        if (String(data.to_id) !== String(CHAT_BADGE_CONFIG.authId)) return;

        var openId = getOpenConversationId();
        var isViewingConversation = isOnChatPage() &&
            openId &&
            String(openId) === String(data.from_id) &&
            !document.hidden;

        if (!isViewingConversation) {
            renderBadge(badgeCount + 1);
        }
        // If they ARE viewing that conversation Chatify's own code.js already
        // appends the message and calls makeSeen(), so no badge change needed.
    }

    // ── Subscribe to Pusher private channel ─────────────────────────────────
    function subscribePusher(PusherLib) {
        var pusherInstance;

        // Re-use the Pusher instance Chatify already created (on chat page)
        if (typeof window.pusher !== 'undefined' && window.pusher.subscribe) {
            pusherInstance = window.pusher;
        } else {
            pusherInstance = new PusherLib(CHAT_BADGE_CONFIG.pusherKey, {
                cluster   : CHAT_BADGE_CONFIG.pusherCluster,
                wsHost    : CHAT_BADGE_CONFIG.pusherHost,
                wsPort    : CHAT_BADGE_CONFIG.pusherPort,
                wssPort   : CHAT_BADGE_CONFIG.pusherPort,
                forceTLS  : CHAT_BADGE_CONFIG.pusherTLS,
                encrypted : CHAT_BADGE_CONFIG.pusherTLS,
                authEndpoint: CHAT_BADGE_CONFIG.pusherAuthUrl,
                auth: {
                    headers: { 'X-CSRF-TOKEN': CHAT_BADGE_CONFIG.csrfToken }
                }
            });
        }

        var channelName = 'private-chatify.' + CHAT_BADGE_CONFIG.authId;
        // Don't double-subscribe if Chatify already subscribed this channel
        var existingChannel = pusherInstance.channel(channelName);
        var ch = existingChannel || pusherInstance.subscribe(channelName);

        // Listen to incoming messages on the private channel
        ch.bind('messaging', function (data) {
            onNewMessage(data);
        });

        // When the user marks a conversation as seen (Chatify fires makeSeen),
        // refresh the badge from the server so it's always accurate.
        ch.bind('client-seen', function () {
            fetchUnreadCount();
        });
    }

    // ── Bootstrap: load Pusher if not yet present, then start ────────────────
    function init() {
        fetchUnreadCount(); // immediate server-side count on page load

        // Pusher SDK is loaded by Chatify's footerLinks on the chat page.
        // On all other pages we need to load it ourselves.
        if (typeof Pusher !== 'undefined') {
            subscribePusher(Pusher);
        } else {
            var script = document.createElement('script');
            script.src = 'https://js.pusher.com/7.2.0/pusher.min.js';
            script.onload = function () { subscribePusher(Pusher); };
            document.head.appendChild(script);
        }

        // Refresh count whenever the tab regains focus (handles tab-switching)
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) fetchUnreadCount();
        });
    }

    // Wait for DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
</body>

</html>
