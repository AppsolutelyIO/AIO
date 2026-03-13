<section class="locations-dropdown py-5">
    <div class="locations-dropdown__container container-xl">
        <!-- Section Header -->
        @if ($displayOptions['title'] || $displayOptions['subtitle'] || $displayOptions['description'])
            <div class="locations-dropdown__header text-center mb-5">
                @if ($displayOptions['title'])
                    <h2 class="locations-dropdown__title h1 mb-3">
                        {{ $displayOptions['title'] }}
                    </h2>
                @endif

                @if ($displayOptions['subtitle'])
                    <h3 class="locations-dropdown__subtitle h4 text-muted mb-4">
                        {{ $displayOptions['subtitle'] }}
                    </h3>
                @endif

                @if ($displayOptions['description'])
                    <p class="locations-dropdown__description lead text-muted">
                        {{ $displayOptions['description'] }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Store Locations Dropdown Content -->
        @if (!empty($displayOptions['locations']))
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Location Dropdown -->
                    <div class="locations-dropdown__select-wrap mb-4">
                        <label for="store-location-select" class="form-label fw-semibold">Select a Store
                            Location:</label>
                        <select id="store-location-select" class="form-select form-select-lg"
                            aria-label="Select a store location" onchange="showSelectedLocation(this.value)">
                            <option value="">Choose a location...</option>
                            @foreach ($displayOptions['locations'] as $index => $location)
                                <option value="{{ $index }}" data-location='@json($location)'>
                                    {{ $location['name'] }}
                                    @if ($location['type'] ?? false)
                                        - {{ $location['type'] }}
                                    @endif
                                    @if ($location['featured'] ?? false)
                                        ⭐
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selected Location Display -->
                    <div id="selected-location-display" class="locations-dropdown__location-details d-none">
                        <div class="card">
                            <div class="card-body p-4">
                                <!-- Location Header -->
                                <div
                                    class="locations-dropdown__header-row d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <h4 id="selected-location-name"
                                            class="locations-dropdown__location-name card-title mb-1"></h4>
                                        <p id="selected-location-type"
                                            class="locations-dropdown__location-type text-muted small mb-0 text-uppercase">
                                        </p>
                                    </div>
                                    <div id="featured-badge" class="d-none">
                                        <span class="badge bg-primary">Featured</span>
                                    </div>
                                </div>

                                <!-- Location Information -->
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="locations-dropdown__location-info mb-4">
                                            <!-- Address -->
                                            <div class="d-flex align-items-start mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                                    <path
                                                        d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                                                </svg>
                                                <div class="flex-grow-1">
                                                    <div id="selected-location-address"></div>
                                                </div>
                                            </div>

                                            <!-- Phone -->
                                            <div id="phone-section"
                                                class="locations-dropdown__phone d-flex align-items-center mb-3 d-none">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                </svg>
                                                <a id="selected-location-phone" href="#"
                                                    class="text-decoration-none text-reset"></a>
                                            </div>

                                            <!-- Email -->
                                            <div id="email-section"
                                                class="locations-dropdown__email d-flex align-items-center mb-3 d-none">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path
                                                        d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                    <path d="M3 7l9 6l9 -6" />
                                                </svg>
                                                <a id="selected-location-email" href="#"
                                                    class="text-decoration-none text-reset"></a>
                                            </div>

                                            <!-- Website -->
                                            <div id="website-section"
                                                class="locations-dropdown__website d-flex align-items-center mb-3 d-none">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler me-2 text-muted flex-shrink-0" width="20"
                                                    height="20" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                    <path d="M3.6 9h16.8" />
                                                    <path d="M3.6 15h16.8" />
                                                    <path d="M11.5 3a17 17 0 0 0 0 18" />
                                                    <path d="M12.5 3a17 17 0 0 1 0 18" />
                                                </svg>
                                                <a id="selected-location-website" href="#" target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-decoration-none text-reset"></a>
                                            </div>

                                            <!-- Services -->
                                            <div id="services-section"
                                                class="locations-dropdown__services mb-4 d-none">
                                                <div class="small text-muted mb-2 fw-semibold">Available Services</div>
                                                <div id="selected-location-services" class="d-flex flex-wrap gap-1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <!-- Hours -->
                                        <div id="hours-section" class="locations-dropdown__hours mb-4 d-none">
                                            <div id="selected-location-hours"></div>
                                        </div>

                                        <!-- Service Hours -->
                                        <div id="service-hours-section"
                                            class="locations-dropdown__service-hours mb-4 d-none">
                                            <div id="selected-location-service-hours"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div id="additional-info"
                                            class="locations-dropdown__additional row d-none">
                                            <div id="manager-section"
                                                class="locations-dropdown__manager col-md-6 mb-3 d-none">
                                                <div class="small text-muted mb-1 fw-semibold">Store Manager</div>
                                                <div id="selected-location-manager" class="fw-medium"></div>
                                            </div>
                                            <div id="established-section"
                                                class="locations-dropdown__established col-md-6 mb-3 d-none">
                                                <div class="small text-muted mb-1 fw-semibold">Established</div>
                                                <div id="selected-location-established" class="fw-medium"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Selection Message -->
                    <div id="no-selection-message" class="locations-dropdown__empty text-center py-5">
                        <div class="text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg mb-3" width="48" height="48"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                                <path
                                    d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                            </svg>
                            <h5>Select a location above to view details</h5>
                            <p class="mb-0">Choose from our {{ count($displayOptions['locations']) }} store locations
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
