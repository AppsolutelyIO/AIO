<section class="locations py-5">
    <div class="locations__container container-xl">
        <!-- Section Header -->
        @if ($displayOptions['title'] || $displayOptions['subtitle'] || $displayOptions['description'])
            <div class="locations__header text-center mb-5">
                @if ($displayOptions['title'])
                    <h2 class="locations__title h1 mb-3">
                        {{ $displayOptions['title'] }}
                    </h2>
                @endif

                @if ($displayOptions['subtitle'])
                    <h3 class="locations__subtitle h4 text-muted mb-4">
                        {{ $displayOptions['subtitle'] }}
                    </h3>
                @endif

                @if ($displayOptions['description'])
                    <p class="locations__description lead text-muted">
                        {{ $displayOptions['description'] }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Store Locations Content -->
        @if (!empty($displayOptions['locations']))
            @if ($displayOptions['layout'] === 'grid')
                <!-- Grid Layout -->
                <div class="row row-deck row-cards g-4">
                    @foreach ($displayOptions['locations'] as $location)
                        @if ($location['show'] ?? false)
                            <div class="col-lg-{{ 12 / $displayOptions['columns'] }} col-md-6 mb-4">
                                <div
                                    class="locations__card card h-100 {{ $location['featured'] ?? false ? 'border-primary' : '' }}">

                                    @if ($location['featured'] ?? false)
                                        <div class="card-stamp">
                                            <div class="card-stamp-icon bg-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z" />
                                                </svg>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h5 class="locations__card-name card-title mb-1">
                                                {{ $location['name'] }}</h5>
                                            @if ($location['type'] ?? false)
                                                <p class="text-muted small mb-0 text-uppercase">
                                                    {{ $location['type'] }}</p>
                                            @endif
                                        </div>

                                        <div class="locations__card-info mb-3">
                                            <div class="d-flex align-items-start mb-2">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                    <path
                                                        d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                </svg>
                                                <div class="flex-grow-1">
                                                    @if (isset($location['latitude']) && isset($location['longitude']))
                                                        <a href="javascript:void(0)"
                                                            class="text-decoration-none text-reset"
                                                            data-map-lat="{{ $location['latitude'] }}"
                                                            data-map-lng="{{ $location['longitude'] }}"
                                                            data-map-name="{{ $location['name'] ?? '' }}"
                                                            aria-label="Open map for {{ $location['name'] }}">{{ $location['address'] }}</a>
                                                    @else
                                                        <div>{{ $location['address'] }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            @if ($location['phone'] ?? false)
                                                <div class="d-flex align-items-center mb-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                    </svg>
                                                    <a href="tel:{{ $location['phone'] }}"
                                                        class="text-decoration-none text-reset">{{ $location['phone'] }}</a>
                                                </div>
                                            @endif

                                            @if ($location['hours'] ?? false)
                                                <div class="small mb-1 fw-semibold">Vehicle Sales</div>
                                                @php($__hoursLines = preg_split('/\s*,\s*/', $location['hours']))
                                                <div class="d-flex align-items-start mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                        <path d="M12 7v5l3 3" />
                                                    </svg>
                                                    <div class="small text-muted w-100">
                                                        <table class="table table-sm table-borderless mb-0 align-middle w-auto">
                                                            <tbody>
                                                                @foreach ($__hoursLines as $__line)
                                                                    @php($__parts = explode(':', $__line, 2))
                                                                    <tr>
                                                                        <td class="pe-2 text-nowrap">
                                                                            {{ trim($__parts[0] ?? '') }}</td>
                                                                        <td class="ps-2 text-muted">
                                                                            {{ str_replace(':00', '', strtolower(trim($__parts[1] ?? ''))) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($location['service_hours'] ?? false)
                                                <div class="small mb-1 fw-semibold">Servicing & Parts</div>
                                                @php($__hoursLines = preg_split('/\s*,\s*/', $location['service_hours']))
                                                <div class="d-flex align-items-start mb-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                        height="20" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                        <path d="M12 7v5l3 3" />
                                                    </svg>
                                                    <div class="small text-muted w-100">
                                                        <table class="table table-sm table-borderless mb-0 align-middle w-auto">
                                                            <tbody>
                                                                @foreach ($__hoursLines as $__line)
                                                                    @php($__parts = explode(':', $__line, 2))
                                                                    <tr>
                                                                        <td class="pe-2 text-nowrap">
                                                                            {{ trim($__parts[0] ?? '') }}</td>
                                                                        <td class="ps-2 text-muted">
                                                                            {{ str_replace(':00', '', strtolower(trim($__parts[1] ?? ''))) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        @if (!empty($location['services']))
                                            <div class="locations__services">
                                                <div class="small text-muted mb-2 fw-semibold">Available Services:</div>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach ($location['services'] as $service)
                                                        <span class="badge bg-primary-lt">{{ $service }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @elseif($displayOptions['layout'] === 'list')
                <!-- List Layout -->
                <div class="locations__list">
                    @foreach ($displayOptions['locations'] as $location)
                        <div
                            class="locations__list-item card mb-3 {{ $location['featured'] ?? false ? 'border-primary' : '' }}">
                            <div class="card-body">
                                <div class="row align-items-start">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="locations__card-name card-title mb-1">
                                                    {{ $location['name'] }}
                                                </h5>
                                                @if ($location['type'] ?? false)
                                                    <p class="text-muted small mb-0 text-uppercase">
                                                        {{ $location['type'] }}</p>
                                                @endif
                                            </div>
                                            @if ($location['featured'] ?? false)
                                                <span class="badge bg-primary">Featured</span>
                                            @endif
                                        </div>

                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="locations__card-info mb-3">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon icon-tabler me-2 text-muted flex-shrink-0"
                                                            width="20" height="20" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                            <path
                                                                d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                        </svg>
                                                        <div class="flex-grow-1">
                                                            @if (isset($location['latitude']) && isset($location['longitude']))
                                                                <a href="javascript:void(0)"
                                                                    class="text-decoration-none text-reset"
                                                                    data-map-lat="{{ $location['latitude'] }}"
                                                                    data-map-lng="{{ $location['longitude'] }}"
                                                                    data-map-name="{{ $location['name'] ?? '' }}"
                                                                    aria-label="Open map for {{ $location['name'] }}">{{ $location['address'] }}</a>
                                                            @else
                                                                <div>{{ $location['address'] }}</div>
                                                            @endif
                                                            <div class="text-muted small">
                                                                @if ($location['city'] ?? false)
                                                                    {{ $location['city'] }}
                                                                @endif
                                                                @if ($location['state'] ?? false)
                                                                    , {{ $location['state'] }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if ($location['phone'] ?? false)
                                                        <div class="d-flex align-items-center mb-3">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler me-2 text-muted flex-shrink-0"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path
                                                                    d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                            </svg>
                                                            <a href="tel:{{ $location['phone'] }}"
                                                                class="text-decoration-none text-reset">{{ $location['phone'] }}</a>
                                                        </div>
                                                    @endif

                                                    @if ($location['hours'] ?? false)
                                                        <div class="small text-muted mb-1 fw-semibold">Vehicle Sales</div>
                                                        @php($__hoursLines = preg_split('/\s*,\s*/', $location['hours']))
                                                        <div class="d-flex align-items-start">
                                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                                class="icon icon-tabler me-2 text-muted flex-shrink-0"
                                                                width="20" height="20" viewBox="0 0 24 24"
                                                                stroke-width="2" stroke="currentColor" fill="none">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                <path d="M12 7v5l3 3" />
                                                            </svg>
                                                            <div class="small text-muted lh-sm w-100">
                                                                <table
                                                                    class="table table-sm table-borderless mb-0 align-middle w-auto">
                                                                    <tbody>
                                                                        @foreach ($__hoursLines as $__line)
                                                                            @php($__parts = explode(':', $__line, 2))
                                                                            <tr>
                                                                                <td class="pe-2 fw-semibold text-nowrap">
                                                                                    {{ trim($__parts[0] ?? '') }}</td>
                                                                                <td class="ps-2 text-muted">
                                                                                    {{ trim($__parts[1] ?? '') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                @if (!empty($location['services']))
                                                    <div class="locations__services mb-4">
                                                        <div class="small text-muted mb-2 fw-semibold">Available Services:
                                                        </div>
                                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                                            @foreach ($location['services'] as $service)
                                                                <span
                                                                    class="badge bg-primary-lt">{{ $service }}</span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Table Layout -->
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Store Name</th>
                                            <th>Address</th>
                                            <th>Phone</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($displayOptions['locations'] as $location)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $location['name'] }}</div>
                                                    @if ($location['type'] ?? false)
                                                        <small class="text-muted">{{ $location['type'] }}</small>
                                                    @endif
                                                    @if ($location['featured'] ?? false)
                                                        <span class="badge bg-primary ms-1">Featured</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (isset($location['latitude']) && isset($location['longitude']))
                                                        <a href="javascript:void(0)"
                                                            class="text-decoration-none text-reset"
                                                            data-map-lat="{{ $location['latitude'] }}"
                                                            data-map-lng="{{ $location['longitude'] }}"
                                                            data-map-name="{{ $location['name'] ?? '' }}"
                                                            aria-label="Open map for {{ $location['name'] }}">{{ $location['address'] }}</a>
                                                    @else
                                                        {{ $location['address'] }}
                                                    @endif
                                                    @if ($location['city'] ?? false)
                                                        <br><small class="text-muted">{{ $location['city'] }}@if ($location['state'] ?? false)
                                                                , {{ $location['state'] }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($location['phone'] ?? false)
                                                        <a href="tel:{{ $location['phone'] }}"
                                                            class="text-decoration-none">{{ $location['phone'] }}</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($location['hours'] ?? false)
                                                        @php($__hoursLines = preg_split('/\s*,\s*/', $location['hours']))
                                                        <small>
                                                            @foreach ($__hoursLines as $__line)
                                                                @php($__parts = explode(':', $__line, 2))
                                                                <div><span
                                                                        class="me-2">{{ trim($__parts[0] ?? '') }}:</span>{{ trim($__parts[1] ?? '') }}
                                                                </div>
                                                            @endforeach
                                                        </small>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Optional Map Integration -->
        @if ($displayOptions['show_map'] && !empty($displayOptions['locations']) && $displayOptions['map_api_key'])
            <div class="locations__map-section mt-5">
                <h4 class="locations__map-title mb-3">Store Locations Map</h4>
                <div id="locations-map" class="locations__map card"
                    style="height: 400px; border-radius: 8px;"></div>
            </div>

            <script>
                function initStoreMap() {
                    const map = new google.maps.Map(document.getElementById('locations-map'), {
                        zoom: 10,
                        center: {
                            lat: {{ $displayOptions['locations'][0]['latitude'] ?? 0 }},
                            lng: {{ $displayOptions['locations'][0]['longitude'] ?? 0 }}
                        }
                    });

                    const locations = @json($displayOptions['locations']);

                    locations.forEach(location => {
                        if (location.latitude && location.longitude) {
                            const marker = new google.maps.Marker({
                                position: {
                                    lat: parseFloat(location.latitude),
                                    lng: parseFloat(location.longitude)
                                },
                                map: map,
                                title: location.name
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: `
                                    <div>
                                        <h6>${location.name}</h6>
                                        <p class="mb-1">${location.address}</p>
                                        ${location.phone ? `<p class="mb-0"><small>${location.phone}</small></p>` : ''}
                                    </div>
                                `
                            });

                            marker.addListener('click', () => {
                                infoWindow.open(map, marker);
                            });
                        }
                    });
                }
            </script>
            <script async defer
                src="https://maps.googleapis.com/maps/api/js?key={{ $displayOptions['map_api_key'] }}&callback=initStoreMap">
            </script>
        @endif
    </div>
</section>
