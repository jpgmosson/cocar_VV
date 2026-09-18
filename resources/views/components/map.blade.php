<x-layout>
    <x-slot:head>
        <script src="{{ asset('vendor/maplibre-gl.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('vendor/maplibre-gl.css') }}">
    </x-slot:head>
    <x-slot:title>
        {{ $title }}
    </x-slot:title>

    <x-slot:body>
        <main class="map-wrapper">
            <div id="map" class="map-canvas"></div>
            <script>
                const map = new maplibregl.Map({
                    container: "map",
                    style: "https://tiles.openfreemap.org/styles/bright",
                    center: [-49.2731, -25.4278],
                    zoom: 12,
                })

                const driverIcon =
                    "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij4KCTxwYXRoIGZpbGw9IiM3YjlkNzgiIGQ9Ik0xNCA1YTEgMSAwIDAgMSAuNjk0LjI4bC4wODcuMDk1TDE4LjQ4IDEwSDE5YTMgMyAwIDAgMSAyLjk5NSAyLjgyNEwyMiAxM3Y0YTEgMSAwIDAgMS0xIDFoLTEuMTcxYTMuMDAxIDMuMDAxIDAgMCAxLTUuNjU4IDBIOS44MjlhMy4wMDEgMy4wMDEgMCAwIDEtNS42NTggMEgzYTEgMSAwIDAgMS0xLTF2LTZsLjAwNy0uMTE3bC4wMDgtLjA1NmwuMDE3LS4wNzhsLjAxMi0uMDM2bC4wMTQtLjA1bDIuMDE0LTUuMDM0QTEgMSAwIDAgMSA1IDV6TTcgMTZhMSAxIDAgMSAwIDAgMmExIDEgMCAwIDAgMC0ybTEwIDBhMSAxIDAgMSAwIDAgMmExIDEgMCAwIDAgMC0ybS02LTlINS42NzZsLTEuMiAzSDExem0yLjUyIDBIMTN2M2gyLjkyeiIgLz4KPC9zdmc+Cgo="

                map.on('load', () => {
                    map.addSource("route-source", {
                        type: "geojson",
                        data: {
                            type: "FeatureCollection",
                            features: [],
                        },
                    });

                    map.addLayer({
                        id: "route-layer",
                        type: "line",
                        source: "route-source",
                        layout: {
                            "line-join": "round",
                            "line-cap": "round",
                        },
                        paint: {
                            "line-color": "#365691",
                            "line-width": 5,
                        },
                    });

                    map.addSource('driver-source', {
                        type: 'geojson',
                        data: {
                            type: 'FeatureCollection',
                            features: []

                        }
                    });
                    const img = new Image(36, 36);
                    img.onload = () => {
                        map.addImage('driver-icon', img);
                        map.addLayer({
                            id: 'route-marker',
                            type: 'symbol',
                            source: 'driver-source',
                            layout: {
                                'icon-image': 'driver-icon',
                                'symbol-placement': 'point',
                                'icon-allow-overlap': true,
                                'icon-size': 1
                            }
                        });
                    };
                    img.src = driverIcon;
                });

                function atualizarRota(trajeto) {
                    map.getSource('route-source').setData(trajeto.rota);
                    map.getSource('driver-source').setData({
                        type: 'Feature',
                        geometry: {
                            type: 'Point',
                            coordinates: trajeto.localizacao_motorista
                        }
                    });

                }
            </script>
            {{ $content }}
        </main>
    </x-slot:body>
</x-layout>
