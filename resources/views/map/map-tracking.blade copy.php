<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Real-Time Vehicle Tracking</title>

    <!-- Wialon SDK -->
    <script type="text/javascript" src="https://hst-api.wialon.com/wsdk/script/wialon.js"></script>

    <!-- Leaflet Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        #container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 10px; }
        #status { padding: 10px 15px; border-radius: 5px; margin: 10px 0; font-weight: bold; }
        .loading { background: #fff3cd; color: #856404; }
        .online { background: #d4edda; color: #155724; }
        .offline { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }

        .login-section { padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 20px; }
        .login-tabs { display: flex; gap: 8px; margin-bottom: 15px; justify-content: center; }
        .login-tabs button { padding: 8px 16px; border: 1px solid #ddd; background: white; border-radius: 5px; cursor: pointer; font-size: 13px; }
        .login-tabs button.active { background: #0066cc; color: white; border-color: #0066cc; }
        .login-panel { display: none; text-align: center; }
        .login-panel.active { display: block; }
        .login-panel input { padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 5px; width: 200px; }
        .login-section button.action-btn { padding: 12px 28px; font-size: 15px; background: #0066cc; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .login-section button.action-btn:hover { background: #0055aa; }
        .login-section .demo-btn { background: #28a745; }
        .login-section .demo-btn:hover { background: #218838; }
        .login-section .create-btn { background: #ffc107; color: #333; }
        .login-section .create-btn:hover { background: #e0a800; }
        .login-section .logout-btn { background: #dc3545; }
        .login-section .logout-btn:hover { background: #c82333; }
        .top-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 10px; }
        .hidden { display: none !important; }

        #toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0; align-items: center; }
        #toolbar select { padding: 8px 12px; border-radius: 5px; border: 1px solid #ddd; min-width: 220px; }
        #map { height: 500px; width: 100%; border-radius: 8px; border: 1px solid #ddd; }

        #info { display: flex; flex-wrap: wrap; gap: 20px; margin: 10px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
        #info .item { flex: 1; min-width: 120px; }
        #info .label { font-size: 12px; color: #888; text-transform: uppercase; }
        #info .value { font-size: 18px; font-weight: bold; color: #333; }

        #vehicle-list { margin-top: 10px; max-height: 220px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; padding: 10px; }
        #vehicle-list ul { list-style: none; padding: 0; margin: 5px 0; }
        #vehicle-list li { padding: 8px 10px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
        #vehicle-list li:hover { background: #e3f2fd; }
        .status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 10px; flex-shrink: 0; }
        .status-dot.online { background: #28a745; }
        .status-dot.offline { background: #dc3545; }

        #debug-info { background: #212529; color: #adf; padding: 10px; border-radius: 5px; font-size: 12px; margin-top: 10px; font-family: monospace; max-height: 160px; overflow-y: auto; }
    </style>
</head>
<body>
    <div id="container">
        <h1>🚗 Real-Time Vehicle Tracking</h1>

        <!-- Login Section -->
        <div id="login-section" class="login-section">
            <div class="login-tabs">
                <button id="tab-demo" class="active" onclick="switchTab('demo')">Demo Account</button>
                <button id="tab-creds" onclick="switchTab('creds')">Username / Password</button>
                <button id="tab-token" onclick="switchTab('token')">Token Login</button>
            </div>

            <div id="panel-demo" class="login-panel active">
                <p style="font-size:13px;color:#666;">Uses the public demo account (wialon_test / test).</p>
                <button class="action-btn demo-btn" onclick="quickLogin()">🚀 Login with Demo Account</button>
            </div>

            <div id="panel-creds" class="login-panel">
                <p style="font-size:12px;color:#a00;">Note: the public "wialon_test" demo account may reject direct username/password login (error 7/8). Use your real account here, or use the Token tab for the demo.</p>
                <input type="text" id="input-username" placeholder="Username" />
                <input type="password" id="input-password" placeholder="Password" />
                <br/>
                <button class="action-btn" onclick="loginWithCredentials()">🔑 Login</button>
            </div>

            <div id="panel-token" class="login-panel">
                <p style="font-size:13px;color:#666;">Recommended for production — opens Wialon's hosted login and never exposes the client's password to this app.</p>
                <button class="action-btn" onclick="openLoginWindow()">🔑 Login via Wialon (Token)</button>
            </div>

            <div class="top-actions">
                <button id="create-btn" class="action-btn create-btn hidden" onclick="createTestVehicle()">➕ Create Test Vehicle</button>
                <button id="logout-btn" class="action-btn logout-btn hidden" onclick="logout()">Logout</button>
            </div>
            <div style="margin-top:10px;font-size:12px;color:#666;text-align:center;">
                Session: <span id="token-display">Not logged in</span>
            </div>
        </div>

        <!-- Status -->
        <div id="status" class="loading">⏳ Initializing...</div>

        <!-- Toolbar -->
        <div id="toolbar">
            <label for="vehicle-select">Select Vehicle:</label>
            <select id="vehicle-select" disabled>
                <option value="">-- Login to load vehicles --</option>
            </select>
            <span style="margin-left:auto;" id="vehicle-count">Vehicles: 0</span>
        </div>

        <!-- Map -->
        <div id="map"></div>

        <!-- Info Panel -->
        <div id="info">
            <div class="item"><div class="label">Selected Vehicle</div><div class="value" id="selected-name">-</div></div>
            <div class="item"><div class="label">Latitude</div><div class="value" id="selected-lat">-</div></div>
            <div class="item"><div class="label">Longitude</div><div class="value" id="selected-lng">-</div></div>
            <div class="item"><div class="label">Speed</div><div class="value" id="selected-speed">- km/h</div></div>
            <div class="item"><div class="label">Last Update</div><div class="value" id="selected-time">-</div></div>
        </div>

        <!-- Vehicle List -->
        <div id="vehicle-list">
            <b>All Vehicles:</b>
            <ul id="vehicles-list"></ul>
        </div>

        <!-- Debug Info -->
        <div id="debug-info">🔍 Waiting for page load...</div>
    </div>

    <script>
        // ============================================
        // CONFIG
        // ============================================
        var WIALON_API_URL = 'https://hst-api.wialon.com';
        // A unit is shown as OFFLINE if its last message is older than this many seconds.
        var OFFLINE_THRESHOLD_SECONDS = 15 * 60; // 15 minutes
        var POLL_INTERVAL_MS = 5000;

        // ============================================
        // GLOBAL STATE
        // ============================================
        var session = null;
        var map = null;
        var markers = {};
        var vehicleData = {};      // id -> {id, name, lat, lng, speed, time, unit}
        var selectedVehicleId = null;
        var pollTimer = null;
        var currentUnits = [];     // array of wialon unit objects

        // ============================================
        // DEBUG / STATUS HELPERS
        // ============================================
        function debug(msg) {
            var el = document.getElementById('debug-info');
            el.innerHTML += '<br>🔹 ' + msg;
            el.scrollTop = el.scrollHeight;
            console.log('[DEBUG]', msg);
        }

        function updateStatus(type, message) {
            var el = document.getElementById('status');
            el.className = type;
            el.textContent = message;
        }

        // Never throws - falls back to a raw code if the SDK's error-text
        // lookup isn't available, so a failed login can never freeze the UI.
        function errText(code) {
            try {
                if (typeof wialon !== 'undefined' && wialon.core && wialon.core.Errors &&
                    typeof wialon.core.Errors.getErrorText === 'function') {
                    var t = wialon.core.Errors.getErrorText(code);
                    if (t) return t + ' (code ' + code + ')';
                }
            } catch (e) { /* fall through */ }
            return 'error code ' + code;
        }

        // Wraps a remoteCall/callback in a timeout so a hung request always
        // surfaces visibly instead of leaving the UI stuck on "Loading...".
        function withTimeout(label, ms, callback) {
            var done = false;
            var timer = setTimeout(function () {
                if (done) return;
                done = true;
                debug('⏱️ Timed out waiting for "' + label + '" after ' + (ms / 1000) + 's.');
                updateStatus('offline', '⏱️ Request "' + label + '" timed out. Check your connection and try again.');
            }, ms);

            return function (code, data) {
                if (done) return; // timeout already fired, ignore late response
                done = true;
                clearTimeout(timer);
                callback(code, data);
            };
        }

        // ============================================
        // LOGIN TABS
        // ============================================
        function switchTab(name) {
            ['demo', 'creds', 'token'].forEach(function (t) {
                document.getElementById('tab-' + t).classList.toggle('active', t === name);
                document.getElementById('panel-' + t).classList.toggle('active', t === name);
            });
        }

        // ============================================
        // MAP INIT
        // ============================================
        function initMap() {
            debug('🗺️ Initializing map...');
            map = L.map('map').setView([33.6844, 73.0479], 6); // default centered near Islamabad
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.control.scale().addTo(map);
            debug('✅ Map initialized');
        }

        function ensureSession() {
            if (!session) {
                session = wialon.core.Session.getInstance();
                session.initSession(WIALON_API_URL);
            }
            return session;
        }

        // ============================================
        // LOGIN METHODS
        // ============================================
        function quickLogin() {
            debug('🚀 Logging in with demo account...');
            updateStatus('loading', '⏳ Logging in with demo account...');

            if (typeof wialon === 'undefined') {
                debug('❌ Wialon SDK is not loaded (check your internet connection / script tag).');
                updateStatus('offline', '❌ SDK not loaded');
                return;
            }

            ensureSession();
            session.login('wialon_test', 'test', withTimeout('demo login', 15000, function (code) {
                if (code) {
                    debug('❌ Demo login failed: ' + errText(code));
                    updateStatus('offline', '❌ Demo login failed: ' + errText(code));
                    return;
                }
                document.getElementById('token-display').textContent = 'Demo account (wialon_test)';
                onLoginSuccess();
            }));
        }

        function loginWithCredentials() {
            var user = document.getElementById('input-username').value.trim();
            var pass = document.getElementById('input-password').value;

            if (!user || !pass) {
                alert('Please enter both username and password.');
                return;
            }

            debug('🔑 Logging in as "' + user + '"...');
            updateStatus('loading', '⏳ Logging in...');

            if (typeof wialon === 'undefined') {
                debug('❌ Wialon SDK is not loaded.');
                updateStatus('offline', '❌ SDK not loaded');
                return;
            }

            ensureSession();
            session.login(user, pass, withTimeout('login', 15000, function (code) {
                if (code) {
                    debug('❌ Login failed: ' + errText(code));
                    updateStatus('offline', '❌ Login failed: ' + errText(code) +
                        (code === 7 ? ' — this account may not allow direct username/password API login (try Token Login instead).' : ''));
                    return;
                }
                document.getElementById('token-display').textContent = 'User: ' + user;
                onLoginSuccess();
            }));
        }

        function openLoginWindow() {
            debug('🔑 Opening hosted Wialon login window...');
            var dns = 'https://hosting.wialon.com';
            var url = dns + '/login.html' +
                '?client_id=App' +
                '&access_type=0x100' +
                '&activation_time=0' +
                '&duration=604800' +
                '&flags=0x1' +
                '&redirect_uri=' + dns + '/post_token.html';

            var handler = function (e) {
                var msg = e.data;
                if (typeof msg === 'string' && msg.indexOf('access_token=') >= 0) {
                    var token = msg.replace('access_token=', '');
                    debug('✅ Token received.');
                    loginWithToken(token);
                    window.removeEventListener('message', handler);
                }
            };
            window.addEventListener('message', handler);
            window.open(url, '_blank', 'width=760,height=500,top=300,left=500');
        }

        function loginWithToken(token) {
            debug('🔑 Logging in with token...');
            updateStatus('loading', '⏳ Logging in with token...');

            ensureSession();
            session.loginToken(token, '', withTimeout('token login', 15000, function (code) {
                if (code) {
                    debug('❌ Token login failed: ' + errText(code));
                    updateStatus('offline', '❌ Token login failed: ' + errText(code));
                    localStorage.removeItem('wialon_saved_token');
                    return;
                }
                // Save so a page refresh can auto-login instead of forcing a fresh popup login.
                try { localStorage.setItem('wialon_saved_token', token); } catch (e) { /* storage unavailable, ignore */ }
                document.getElementById('token-display').textContent = 'Token session';
                onLoginSuccess();
            }));
        }

        function onLoginSuccess() {
            debug('✅ Login successful! Host: ' + (session.getBaseUrl ? session.getBaseUrl() : WIALON_API_URL));
            document.getElementById('login-section').querySelectorAll('.login-tabs, .login-panel').forEach(function (el) {
                el.classList.add('hidden');
            });
            document.getElementById('logout-btn').classList.remove('hidden');
            document.getElementById('create-btn').classList.remove('hidden');
            document.getElementById('vehicle-select').disabled = false;

            updateStatus('online', '✅ Connected successfully!');
            loadVehicles();
        }

        function logout() {
            debug('🔴 Logging out...');
            if (session) {
                try { session.logout(); } catch (e) { /* ignore */ }
            }
            try { localStorage.removeItem('wialon_saved_token'); } catch (e) { /* ignore */ }

            document.getElementById('login-section').querySelectorAll('.login-tabs, .login-panel').forEach(function (el) {
                el.classList.remove('hidden');
            });
            document.getElementById('logout-btn').classList.add('hidden');
            document.getElementById('create-btn').classList.add('hidden');
            document.getElementById('vehicle-select').disabled = true;
            document.getElementById('vehicle-select').innerHTML = '<option value="">-- Login to load vehicles --</option>';
            document.getElementById('token-display').textContent = 'Not logged in';
            document.getElementById('vehicle-count').textContent = 'Vehicles: 0';

            for (var id in markers) { map.removeLayer(markers[id]); }
            markers = {};
            vehicleData = {};
            currentUnits = [];
            selectedVehicleId = null;

            document.getElementById('vehicles-list').innerHTML = '';
            resetInfoPanel();

            if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }

            updateStatus('offline', '🔴 Disconnected');
            debug('🔴 Logged out');
        }

        function resetInfoPanel() {
            document.getElementById('selected-name').textContent = '-';
            document.getElementById('selected-lat').textContent = '-';
            document.getElementById('selected-lng').textContent = '-';
            document.getElementById('selected-speed').textContent = '- km/h';
            document.getElementById('selected-time').textContent = '-';
        }

        // ============================================
        // CREATE TEST VEHICLE
        // ============================================
        function createTestVehicle() {
            debug('➕ Attempting to create a test vehicle...');
            updateStatus('loading', '⏳ Creating test vehicle...');

            var vehicleName = 'Test_Vehicle_' + Date.now();
            var user = session.getCurrUser();
            var creatorId = user ? user.getId() : 0;

            debug('➡️ Calling wialon.core.Remote.getInstance().remoteCall("core/create_unit")...');
            try {
                wialon.core.Remote.getInstance().remoteCall('core/create_unit', {
                    creatorId: creatorId,
                    name: vehicleName,
                    hwTypeId: 6,
                    dataFlags: 1
                }, withTimeout('create_unit', 15000, function (code, data) {
                    if (code) {
                        debug('❌ Create failed: ' + errText(code));
                        updateStatus('warning', '⚠️ Could not create vehicle: ' + errText(code) +
                            '. This account may not have unit-creation rights (common on demo/shared accounts).');
                        return;
                    }
                    if (data && data.item) {
                        debug('✅ Vehicle created: ' + data.item.nm + ' (ID: ' + data.item.id + ')');
                        updateStatus('online', '✅ Vehicle created: ' + vehicleName);
                        setTimeout(loadVehicles, 1000);
                    } else {
                        debug('❌ Unexpected response: ' + JSON.stringify(data));
                        updateStatus('offline', '❌ Failed to create vehicle (unexpected response).');
                    }
                }));
            } catch (ex) {
                debug('💥 Exception thrown calling create_unit: ' + (ex && ex.message ? ex.message : ex));
                updateStatus('offline', '💥 JS exception while creating vehicle — see debug log.');
            }
        }

        // ============================================
        // LOAD VEHICLES
        // ============================================
        function loadVehicles() {
            debug('📦 Searching for vehicles (session.searchItems)...');
            updateStatus('loading', '⏳ Searching vehicles...');

            var searchSpec = {
                itemsType: 'avl_unit',
                propName: 'sys_name',
                propValueMask: '*',
                sortType: 'sys_name'
            };

            // base item flags + last-message flag, so units come back with position data already attached
            var dataFlags = wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.lastMessage;

            try {
                if (typeof session.searchItems !== 'function') {
                    debug('❌ session.searchItems is not a function — unexpected SDK build.');
                    updateStatus('offline', '❌ Internal error: session.searchItems missing.');
                    return;
                }

                session.searchItems(searchSpec, true, dataFlags, 0, 0, withTimeout('searchItems', 20000, function (code, data) {
                    debug('⬅️ searchItems callback fired. code=' + code);
                    if (code) {
                        debug('❌ Search failed: ' + errText(code));
                        updateStatus('offline', '❌ Search failed: ' + errText(code));
                        return;
                    }

                    var units = (data && data.items) ? data.items : [];
                    debug('🔍 Found ' + units.length + ' vehicle(s) in account.');

                    document.getElementById('create-btn').classList.remove('hidden');

                    if (units.length === 0) {
                        updateStatus('warning', '⚠️ No vehicles found in this account. Try "Create Test Vehicle".');
                        document.getElementById('vehicle-count').textContent = 'Vehicles: 0';
                        document.getElementById('vehicles-list').innerHTML =
                            '<li style="color:#888;justify-content:center;">No vehicles. Click "Create Test Vehicle".</li>';
                        return;
                    }

                    onUnitsReady(units);
                }));

                debug('✅ searchItems dispatched (request sent, awaiting response)...');
            } catch (ex) {
                debug('💥 Exception thrown calling searchItems: ' + (ex && ex.message ? ex.message : ex));
                updateStatus('offline', '💥 JS exception while searching vehicles — see debug log.');
            }
        }

        // ============================================
        // UNITS READY -> WIRE UP UI + REAL-TIME UPDATES
        // ============================================
        function onUnitsReady(units) {
            currentUnits = units;
            document.getElementById('vehicle-count').textContent = 'Vehicles: ' + units.length;
            updateStatus('online', '✅ Tracking ' + units.length + ' vehicle(s)');

            populateDropdown(units);
            buildVehicleList(units);
            attachUnitListeners(units);

            // searchItems already returned units with position data attached (dataFlag.lastMessage),
            // so we can paint immediately without waiting on the subscription below.
            refreshFromUnits(units);

            subscribeToLiveUpdates(units);

            // Active server poll — forces the SDK to fetch fresh position data
            // from the server instead of just reading its in-memory cache.
            if (pollTimer) clearInterval(pollTimer);
            pollTimer = setInterval(function () {
                activePoll(units);
            }, POLL_INTERVAL_MS);
        }

        // Subscribes these units to the avl_evts push channel so 'changePosition'
        // listeners actually fire when the server sees new data. Without this,
        // searchItems only gives a one-time snapshot — no live updates.
        //
        // Using type:'col' with mode:0 (set/replace flags) here instead of one
        // type:'id'/mode:1 entry per unit — every official Wialon example
        // subscribes this way for a *first-time* subscription. mode:1 ("add
        // flags to those already present in the session") is documented for
        // topping up flags on units that are already tracked, which these
        // aren't yet at this point, so this removes that as a variable.
        function subscribeToLiveUpdates(units) {
            var flags = wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.lastMessage;

            var unitIds = units.map(function (unit) { return unit.getId(); });
            var spec = [{ type: 'col', data: unitIds, flags: flags, mode: 0 }]; // mode 0 = set/replace flags

            debug('➡️ Subscribing ' + units.length + ' unit(s) to live updates (updateDataFlags)...');
            try {
                session.updateDataFlags(spec, withTimeout('updateDataFlags', 15000, function (code) {
                    if (code) {
                        debug('❌ Live-update subscription failed: ' + errText(code));
                        return;
                    }
                    debug('✅ Subscribed to live updates. Waiting for position pushes...');
                }));
            } catch (ex) {
                debug('💥 Exception subscribing to live updates: ' + (ex && ex.message ? ex.message : ex));
            }
        }

        // ============================================
        // ACTIVE SERVER POLL
        // Forces a fresh fetch of all unit positions from the server.
        // This is the fallback when the push channel (avl_evts) is silent —
        // common on demo accounts where vehicles don't actually move.
        // ============================================
        function activePoll(units) {
            if (!session || !units || units.length === 0) return;

            var unitIds = units.map(function (u) { return u.getId(); });
            var flags = wialon.item.Item.dataFlag.base | wialon.item.Unit.dataFlag.lastMessage;

            // Re-issue updateDataFlags — this forces the SDK to re-fetch
            // unit data (including position) from the server.
            try {
                session.updateDataFlags(
                    [{ type: 'col', data: unitIds, flags: flags, mode: 1 }],
                    function (code) {
                        if (code) {
                            debug('⚠️ Poll updateDataFlags failed: ' + errText(code));
                            return;
                        }
                        // After the SDK updates its internal cache, read from it.
                        refreshFromUnits(units);
                    }
                );
            } catch (ex) {
                // Silent fail — don't spam the debug log on every poll tick.
            }
        }

        function attachUnitListeners(units) {
            units.forEach(function (unit) {
                unit.addListener('changePosition', function () {
                    refreshFromUnits([unit]);
                });
            });
        }

        // ============================================
        // DROPDOWN
        // ============================================
        function populateDropdown(units) {
            var select = document.getElementById('vehicle-select');
            select.innerHTML = '<option value="">-- Select a vehicle --</option>';

            units.forEach(function (unit) {
                var option = document.createElement('option');
                option.value = unit.getId();
                option.textContent = unit.getName() + ' (ID: ' + unit.getId() + ')';
                select.appendChild(option);
            });

            // Wire up the change handler once
            select.onchange = function () {
                var id = parseInt(select.value, 10);
                if (!id) return;
                selectedVehicleId = id;
                focusVehicle(id);
            };
        }

        function focusVehicle(id) {
            var data = vehicleData[id];
            if (data) {
                updateInfoPanel(data);
                map.setView([data.lat, data.lng], 14);
                if (markers[id]) markers[id].openPopup();
            } else {
                updateInfoPanel(null, id);
            }
            highlightListItem(id);
        }

        // ============================================
        // REFRESH POSITIONS FROM UNIT OBJECTS
        // ============================================
        function refreshFromUnits(units) {
            var now = Date.now() / 1000;

            units.forEach(function (unit) {
                var id = unit.getId();
                var pos = unit.getPosition();
                var name = unit.getName();

                if (!pos) {
                    // No position data available at all for this unit yet.
                    setListItemOffline(id);
                    return;
                }

                var lat = pos.y;
                var lng = pos.x;
                var speed = pos.s || 0;
                var time = pos.t || 0;
                var isOnline = (now - time) <= OFFLINE_THRESHOLD_SECONDS;

                vehicleData[id] = { id: id, name: name, lat: lat, lng: lng, speed: speed, time: time, online: isOnline };

                if (markers[id]) {
                    markers[id].setLatLng([lat, lng]);
                    markers[id].setPopupContent(getPopupContent(vehicleData[id]));
                } else {
                    var icon = L.divIcon({
                        className: 'vehicle-marker',
                        html: '<div style="font-size:22px;line-height:1;">🚗</div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    markers[id] = L.marker([lat, lng], { icon: icon })
                        .addTo(map)
                        .bindPopup(getPopupContent(vehicleData[id]));

                    if (Object.keys(markers).length === 1) {
                        map.setView([lat, lng], 12);
                    }
                }

                updateListItem(id, vehicleData[id]);

                if (selectedVehicleId === id) {
                    updateInfoPanel(vehicleData[id]);
                }
            });
        }

        // ============================================
        // UI: POPUP / LIST / INFO PANEL
        // ============================================
        function getPopupContent(data) {
            var time = data.time ? new Date(data.time * 1000).toLocaleString() : 'Unknown';
            return '<b>' + escapeHtml(data.name) + '</b><br>' +
                   '📍 Lat: ' + data.lat.toFixed(6) + '<br>' +
                   '📍 Lng: ' + data.lng.toFixed(6) + '<br>' +
                   '🚀 Speed: ' + data.speed + ' km/h<br>' +
                   '🕐 ' + time + '<br>' +
                   (data.online ? '🟢 Online' : '🔴 Offline (stale)');
        }

        function buildVehicleList(units) {
            var list = document.getElementById('vehicles-list');
            list.innerHTML = '';

            units.forEach(function (unit) {
                var id = unit.getId();
                var name = unit.getName();
                var li = document.createElement('li');
                li.id = 'vehicle-item-' + id;
                li.innerHTML =
                    '<span><span class="status-dot offline" id="dot-' + id + '"></span>' + escapeHtml(name) + '</span>' +
                    '<span id="pos-' + id + '" style="color:#888;font-size:12px;">No data yet</span>';
                li.onclick = function () {
                    document.getElementById('vehicle-select').value = id;
                    selectedVehicleId = id;
                    focusVehicle(id);
                };
                list.appendChild(li);
            });
        }

        function updateListItem(id, data) {
            var dot = document.getElementById('dot-' + id);
            var posEl = document.getElementById('pos-' + id);
            if (dot) dot.className = 'status-dot ' + (data.online ? 'online' : 'offline');
            if (posEl) posEl.textContent = data.lat.toFixed(4) + ', ' + data.lng.toFixed(4) +
                (data.online ? '' : ' (stale)');
        }

        function setListItemOffline(id) {
            var dot = document.getElementById('dot-' + id);
            var posEl = document.getElementById('pos-' + id);
            if (dot) dot.className = 'status-dot offline';
            if (posEl) posEl.textContent = 'No position data';
        }

        function highlightListItem(id) {
            document.querySelectorAll('#vehicle-list li').forEach(function (el) {
                el.style.background = 'transparent';
            });
            var item = document.getElementById('vehicle-item-' + id);
            if (item) item.style.background = '#e3f2fd';
        }

        function updateInfoPanel(data, fallbackId) {
            if (!data) {
                document.getElementById('selected-name').textContent =
                    fallbackId ? ('Vehicle #' + fallbackId + ' (no data yet)') : '-';
                document.getElementById('selected-lat').textContent = '-';
                document.getElementById('selected-lng').textContent = '-';
                document.getElementById('selected-speed').textContent = '- km/h';
                document.getElementById('selected-time').textContent = '-';
                return;
            }
            document.getElementById('selected-name').textContent = data.name;
            document.getElementById('selected-lat').textContent = data.lat.toFixed(6);
            document.getElementById('selected-lng').textContent = data.lng.toFixed(6);
            document.getElementById('selected-speed').textContent = data.speed + ' km/h';
            var time = data.time ? new Date(data.time * 1000).toLocaleString() : 'Unknown';
            document.getElementById('selected-time').textContent = time + (data.online ? '' : ' (stale)');
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str == null ? '' : String(str);
            return div.innerHTML;
        }

        // ============================================
        // PAGE INIT
        // ============================================
        window.onload = function () {
            debug('🚀 Page loaded.');
            initMap();
            updateStatus('offline', '🔴 Not connected — please login.');

            if (typeof wialon === 'undefined') {
                debug('❌ Wialon SDK failed to load — check your internet connection.');
                updateStatus('offline', '❌ SDK not loaded — check your internet connection.');
                return;
            }
            debug('✅ Wialon SDK is available.');

            // Auto-login if we have a token saved from a previous session (survives page refresh).
            var savedToken = null;
            try { savedToken = localStorage.getItem('wialon_saved_token'); } catch (e) { /* ignore */ }

            if (savedToken) {
                debug('🔁 Found saved token — attempting auto-login...');
                loginWithToken(savedToken);
            }
        };
    </script>
</body>
</html>
