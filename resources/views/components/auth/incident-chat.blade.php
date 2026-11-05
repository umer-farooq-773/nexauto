{{--
    Incident Discussion / Chat Component
    Props:
      $incidentType  — snake_case slug  e.g. 'impoundment', 'vehicle_bogged_down'
      $incidentId    — UUID of the incident record
--}}
@php
    use App\Constants\Constants;
    $authUser = auth()->user();
    $canChat  = $authUser && $authUser->hasRole([
        Constants::ADMIN,
        Constants::SUPERADMIN,
        Constants::CHAMPS_MANAGEMENT,
        Constants::CLIENT_MANAGEMENT,
        Constants::CLIENT_DIRECTORS,
    ]);
@endphp

@if($canChat)

{{-- Remix Icon CDN --}}
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

{{-- ── Scoped styles ─────────────────────────────────────────────────────── --}}
<style>
.ic-wrap {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-top: 24px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(15,23,42,0.04);
    transition: box-shadow 0.2s ease;
}
.ic-wrap:hover {
    box-shadow: 0 6px 20px rgba(15,23,42,0.06);
}
.ic-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.ic-header-icon {
    width: 42px;
    height: 42px;
    background: rgba(255,255,255,.18);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.25rem;
    flex-shrink: 0;
    backdrop-filter: blur(6px);
}
.ic-header-text h6 {
    margin: 0;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: -0.01em;
}
.ic-header-text p {
    margin: 2px 0 0;
    color: rgba(255,255,255,.82);
    font-size: 12px;
    line-height: 1.4;
}
.ic-body {
    padding: 16px 20px;
}
/* Message list */
.ic-messages {
    min-height: 140px;
    max-height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 4px 2px;
    margin-bottom: 16px;
}
.ic-messages::-webkit-scrollbar { width: 6px; }
.ic-messages::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.ic-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.ic-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
/* Empty / loading state */
.ic-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 0;
    color: #94a3b8;
    font-size: 13px;
    gap: 8px;
    font-weight: 500;
}
.ic-empty i { font-size: 32px; opacity: .5; }
/* Bubble row */
.ic-row { display: flex; gap: 10px; align-items: flex-end; }
.ic-row.mine  { flex-direction: row-reverse; }
/* Avatar */
.ic-avatar {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    text-transform: uppercase;
    box-shadow: 0 2px 4px rgba(15,23,42,0.1);
}
/* Bubble */
.ic-bubble-wrap { max-width: 68%; display: flex; flex-direction: column; }
.ic-row.mine .ic-bubble-wrap { align-items: flex-end; }
.ic-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}
.ic-row.mine .ic-meta { flex-direction: row-reverse; }
.ic-name { font-size: 12px; font-weight: 700; color: #1e293b; }
.ic-role-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 50px;
    color: #fff;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.ic-bubble {
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13.5px;
    line-height: 1.55;
    word-break: break-word;
    white-space: pre-wrap;
}
/* Other person */
.ic-row:not(.mine) .ic-bubble {
    background: #f8fafc;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
}
/* My messages */
.ic-row.mine .ic-bubble {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
    box-shadow: 0 2px 6px rgba(59,130,246,0.25);
}
.ic-time {
    font-size: 10.5px;
    color: #94a3b8;
    margin-top: 4px;
    padding: 0 2px;
    font-weight: 500;
}
/* Attachment inside bubble */
.ic-attach-img {
    max-width: 200px;
    max-height: 140px;
    border-radius: 8px;
    margin-top: 8px;
    display: block;
    cursor: pointer;
}
.ic-attach-file {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 8px;
    text-decoration: none;
    border: 1px solid rgba(255,255,255,.35);
    color: inherit;
    background: rgba(0,0,0,.08);
    font-weight: 600;
}
.ic-row:not(.mine) .ic-attach-file {
    border-color: #e2e8f0;
    background: #ffffff;
    color: #1e293b;
}
/* Divider */
.ic-divider {
    border: none;
    border-top: 1px solid #f1f5f9;
    margin: 0 0 16px;
}
/* Input area */
.ic-input-area {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}
.ic-textarea-wrap { flex: 1; }
.ic-textarea {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13.5px;
    resize: none;
    outline: none;
    transition: all .2s;
    font-family: inherit;
    color: #1e293b;
    background: #f8fafc;
    line-height: 1.5;
}
.ic-textarea:focus {
    border-color: #3b82f6;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.ic-textarea::placeholder { color: #94a3b8; }
.ic-toolbar {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
}
.ic-attach-btn {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all .2s;
}
.ic-attach-btn:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: #eff6ff;
}
.ic-attach-name {
    font-size: 11.5px;
    color: #64748b;
    max-width: 160px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
}
.ic-send-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0 22px;
    height: 42px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    transition: all .25s ease;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(59,130,246,0.25);
}
.ic-send-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(59,130,246,0.35);
}
.ic-send-btn:active:not(:disabled) { transform: scale(.98); }
.ic-send-btn:disabled { opacity: .6; cursor: not-allowed; box-shadow: none; }
/* Error banner */
.ic-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #b91c1c;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px 14px;
    margin-bottom: 12px;
    display: none;
}
@media print { .ic-wrap { display: none !important; } }
</style>

{{-- ── Markup ────────────────────────────────────────────────────────────── --}}
<div class="ic-wrap" id="incidentChatSection">

    {{-- Header --}}
    <div class="ic-header">
        <div class="ic-header-icon"><i class="ri-chat-3-line"></i></div>
        <div class="ic-header-text">
            <h6>{{ __('Incident Discussion') }}</h6>
            <p>{{ __('Communicate with the team before approving or changing status') }}</p>
        </div>
    </div>

    {{-- Body --}}
    <div class="ic-body">

        {{-- Error banner --}}
        <div class="ic-error" id="icError"></div>

        {{-- Messages --}}
        <div class="ic-messages" id="icMessages">
            <div class="ic-empty" id="icLoading">
                <i class="ri-loader-4-line ri-spin"></i>
                <span>{{ __('Loading messages…') }}</span>
            </div>
        </div>

        <hr class="ic-divider">

        {{-- Input --}}
        <div class="ic-input-area">
            <div class="ic-textarea-wrap">
                <textarea
                    id="icInput"
                    class="ic-textarea"
                    rows="2"
                    placeholder="{{ __('Write a message… (Ctrl + Enter to send)') }}"
                    maxlength="2000"
                ></textarea>
                <div class="ic-toolbar">
                    <label for="icFile" class="ic-attach-btn" title="{{ __('Attach file') }}">
                        <i class="ri-attachment-2"></i> {{ __('Attach') }}
                    </label>
                    <input type="file" id="icFile" class="d-none"
                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx">
                    <span class="ic-attach-name" id="icFileName"></span>
                </div>
            </div>
            <button type="button" class="ic-send-btn" id="icSendBtn" onclick="icSend()">
                <i class="ri-send-plane-fill"></i> {{ __('Send') }}
            </button>
        </div>

    </div>
</div>

{{-- ── Script ────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    /* ── Config ─────────────────────────────────────────────────────────── */
    var TYPE  = @json($incidentType);
    var ID    = @json($incidentId);
    var URL   = '{{ url("my-account/incident-chat") }}/' + TYPE + '/' + ID;
    var CSRF  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    /* ── DOM refs ───────────────────────────────────────────────────────── */
    var msgBox   = document.getElementById('icMessages');
    var errBox   = document.getElementById('icError');
    var input    = document.getElementById('icInput');
    var sendBtn  = document.getElementById('icSendBtn');
    var fileEl   = document.getElementById('icFile');
    var fileName = document.getElementById('icFileName');

    /* ── Expose send globally for onclick ───────────────────────────────── */
    window.icSend = sendMessage;

    /* ── Role colour map ────────────────────────────────────────────────── */
    var roleColors = {
        'super_admin':       '#6f42c1',
        'admin':             '#0b3d91',
        'champs_management': '#198754',
        'client_directors':  '#e67e22',
        'client_management': '#0891b2',
    };

    function roleColor(r) { return roleColors[r] || '#6b7a90'; }

    /* ── Avatar initials ────────────────────────────────────────────────── */
    function initials(name) {
        if (!name) return '?';
        var parts = name.trim().split(' ');
        return parts.length >= 2
            ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
            : name.substring(0, 2).toUpperCase();
    }

    /* ── HTML escape ────────────────────────────────────────────────────── */
    function esc(s) {
        if (!s) return '';
        return String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Render one message ─────────────────────────────────────────────── */
    function renderMsg(c) {
        var mine   = c.is_mine;
        var color  = roleColor(c.role_type);
        var row    = document.createElement('div');
        row.className = 'ic-row' + (mine ? ' mine' : '');
        row.dataset.id = c.id;

        /* attachment html */
        var attachHtml = '';
        if (c.attachment) {
            var ext = c.attachment.split('.').pop().toLowerCase();
            var isImg = ['jpg','jpeg','png','gif','webp'].indexOf(ext) !== -1;
            if (isImg) {
                attachHtml = '<a href="' + esc(c.attachment) + '" target="_blank">'
                           + '<img src="' + esc(c.attachment) + '" class="ic-attach-img" alt="attachment"></a>';
            } else {
                attachHtml = '<a href="' + esc(c.attachment) + '" target="_blank" class="ic-attach-file">'
                           + '<i class="ri-file-text-line"></i> Download Attachment</a>';
            }
        }

        /* message text */
        var msgText = c.message ? esc(c.message).replace(/\n/g,'<br>') : '';

        row.innerHTML =
            '<div class="ic-avatar" style="background:' + color + '">' + esc(initials(c.user_name)) + '</div>'
          + '<div class="ic-bubble-wrap">'
          +   '<div class="ic-meta">'
          +     '<span class="ic-name">' + esc(c.user_name) + '</span>'
          +     '<span class="ic-role-badge" style="background:' + color + '">' + esc(c.role_label) + '</span>'
          +   '</div>'
          +   '<div class="ic-bubble">' + msgText + attachHtml + '</div>'
          +   '<div class="ic-time">' + esc(c.created_at) + '</div>'
          + '</div>';

        return row;
    }

    /* ── Scroll to bottom ───────────────────────────────────────────────── */
    function scrollBottom() { msgBox.scrollTop = msgBox.scrollHeight; }

    /* ── Show / hide error ──────────────────────────────────────────────── */
    function showErr(msg) {
        errBox.textContent = msg;
        errBox.style.display = 'block';
        clearTimeout(showErr._t);
        showErr._t = setTimeout(function(){ errBox.style.display = 'none'; }, 6000);
    }

    /* ── Load messages ──────────────────────────────────────────────────── */
    function loadMessages() {
        fetch(URL, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            }
        })
        .then(function(r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function(data) {
            msgBox.innerHTML = '';
            if (!data.chats || data.chats.length === 0) {
                msgBox.innerHTML =
                    '<div class="ic-empty">'
                  + '<i class="ri-chat-3-line"></i>'
                  + '<span>No messages yet. Start the conversation.</span>'
                  + '</div>';
                return;
            }
            data.chats.forEach(function(c) { msgBox.appendChild(renderMsg(c)); });
            scrollBottom();

            // Mark messages as read when loaded
            markMessagesAsRead();
        })
        .catch(function() {
            msgBox.innerHTML =
                '<div class="ic-empty" style="color:#b91c1c">'
              + '<i class="ri-error-warning-line"></i>'
              + '<span>Could not load messages. Please refresh.</span>'
              + '</div>';
        });
    }

    /* ── Mark messages as read ───────────────────────────────────────────── */
    function markMessagesAsRead() {
        var markReadUrl = '{{ route("incident_chat.mark_read", ["incidentType" => "TYPE", "incidentId" => "ID"]) }}'
            .replace('TYPE', TYPE)
            .replace('ID', ID);

        fetch(markReadUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            }
        })
        .then(function(r) {
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                // Update the navbar badge count
                var badge = document.getElementById('navbar-chat-badge');
                if (badge) {
                    var currentCount = parseInt(badge.textContent) || 0;
                    if (currentCount > 0) {
                        badge.textContent = Math.max(0, currentCount - 1);
                        if (badge.textContent === '0') {
                            badge.style.display = 'none';
                        }
                    }
                }
            }
        })
        .catch(function() {
            // Silently fail - don't show error to user
        });
    }

    /* ── Send message ───────────────────────────────────────────────────── */
    function sendMessage() {
        var msg  = input.value.trim();
        var file = fileEl.files[0];

        if (!msg && !file) {
            input.focus();
            showErr('Please type a message or attach a file before sending.');
            return;
        }

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Sending…';

        var fd = new FormData();
        fd.append('_token', CSRF);
        if (msg)  fd.append('message', msg);
        if (file) fd.append('attachment', file);

        fetch(URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF,
            },
            body: fd,
        })
        .then(function(r) {
            return r.json().then(function(d) { return { status: r.status, data: d }; });
        })
        .then(function(res) {
            if (res.status === 422) {
                var errs = res.data.errors || {};
                var msgs = [];
                Object.values(errs).forEach(function(e) { msgs = msgs.concat(e); });
                showErr(msgs.length ? msgs.join(' ') : 'Validation failed.');
                return;
            }
            if (!res.data.success) {
                showErr(res.data.error || res.data.message || 'Failed to send. Please try again.');
                return;
            }

            /* Remove empty-state placeholder */
            var ph = msgBox.querySelector('.ic-empty');
            if (ph) ph.remove();

            msgBox.appendChild(renderMsg(res.data.chat));
            scrollBottom();

            /* Reset inputs */
            input.value      = '';
            fileEl.value     = '';
            fileName.textContent = '';
        })
        .catch(function() {
            showErr('Network error. Please check your connection and try again.');
        })
        .finally(function() {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="ri-send-plane-fill"></i> Send';
        });
    }

    /* ── File picker label ──────────────────────────────────────────────── */
    fileEl.addEventListener('change', function() {
        fileName.textContent = this.files[0] ? this.files[0].name : '';
    });

    /* ── Ctrl+Enter shortcut ────────────────────────────────────────────── */
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            sendMessage();
        }
    });

    /* ── Boot ───────────────────────────────────────────────────────────── */
    loadMessages();
    setInterval(loadMessages, 30000);
})();
</script>

@endif
