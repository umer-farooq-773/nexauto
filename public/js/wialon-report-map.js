/**
 * wialon-report-map.js
 *
 * Standalone MapLibre + OSM map component. Knows nothing about reports or
 * Wialon API calls — it only draws points/routes it's given. Reuse this same
 * file anywhere else in the app that needs a vehicle map (waybill routes,
 * live tracking, etc.) by calling WialonReportMap.init() with a different
 * container id.
 */
const WialonReportMap = (() => {
    let map = null;
    let markers = [];

    /**
     * @param {string} containerId - DOM element id to render the map into
     * @param {object} [options]
     * @param {number} [options.centerLng=0]
     * @param {number} [options.centerLat=0]
     * @param {number} [options.zoom=5]
     */
    function init(containerId, options = {}) {
        map = new maplibregl.Map({
            container: containerId,
            style: {
                version: 8,
                sources: {
                    osm: {
                        type: 'raster',
                        tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                        tileSize: 256,
                        attribution: '&copy; OpenStreetMap contributors',
                    },
                },
                layers: [{ id: 'osm', type: 'raster', source: 'osm' }],
            },
            center: [options.centerLng ?? 0, options.centerLat ?? 0],
            zoom: options.zoom ?? 5,
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');

        return map;
    }

    /**
     * Replace all markers on the map.
     * @param {Array<{lng:number, lat:number, label?:string}>} points
     */
    function setPoints(points) {
        clearMarkers();

        if (!map || !points || points.length === 0) {
            return;
        }

        const bounds = new maplibregl.LngLatBounds();

        points.forEach((point) => {
            const marker = new maplibregl.Marker({ color: '#e63946' })
                .setLngLat([point.lng, point.lat])
                .setPopup(point.label ? new maplibregl.Popup().setText(point.label) : undefined)
                .addTo(map);

            markers.push(marker);
            bounds.extend([point.lng, point.lat]);
        });

        map.fitBounds(bounds, { padding: 60, maxZoom: 14 });
    }

    /**
     * Draw a route line connecting an ordered list of points.
     * @param {Array<{lng:number, lat:number}>} points
     */
    function setRoute(points) {
        if (!map || !points || points.length < 2) {
            return;
        }

        const geojson = {
            type: 'Feature',
            geometry: {
                type: 'LineString',
                coordinates: points.map((p) => [p.lng, p.lat]),
            },
        };

        const draw = () => {
            if (map.getSource('report-route')) {
                map.getSource('report-route').setData(geojson);
                return;
            }

            map.addSource('report-route', { type: 'geojson', data: geojson });
            map.addLayer({
                id: 'report-route-line',
                type: 'line',
                source: 'report-route',
                paint: { 'line-color': '#1d3557', 'line-width': 3 },
            });
        };

        if (map.isStyleLoaded()) {
            draw();
        } else {
            map.once('load', draw);
        }
    }

    function clearMarkers() {
        markers.forEach((m) => m.remove());
        markers = [];
    }

    function getMap() {
        return map;
    }

    return { init, setPoints, setRoute, clearMarkers, getMap };
})();
