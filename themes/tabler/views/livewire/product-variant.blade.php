@php
    $product = $displayOptions['product'] ?? [];
    $variants = $product['variants'] ?? [];

    // Pre-process all image URLs through asset_url() helper
    foreach ($variants as &$variant) {
        if (!empty($variant['colors'])) {
            foreach ($variant['colors'] as &$color) {
                if (!empty($color['images'])) {
                    $color['images'] = array_map(fn($img) => asset_url($img), $color['images']);
                }
            }
        }
    }
    unset($variant, $color);

    $product['variants'] = $variants;

    // Pre-compute first variant and color for SSR fallback
    $firstVariant = $variants[0] ?? null;
    $firstColor = $firstVariant['colors'][0] ?? null;
    $firstImage = $firstColor['images'][0] ?? null;

    // Generate a unique ID for this block instance
    $blockId = 'pvb-' . $this->getId();
@endphp

<section class="product-variant py-5" id="{{ $blockId }}" wire:key="product-variant-{{ $this->getId() }}">
    <div class="product-variant__container container-xl">
        @if (empty($product))
            <div class="alert alert-warning">No product data configured</div>
        @elseif (empty($variants))
            <div class="product-variant__header mb-4 text-center">
                <h1 class="h2 fw-bold mb-2">{{ $product['name'] ?? 'Product' }}</h1>
            </div>
            <div class="alert alert-info">No variants available</div>
        @else
            <div wire:ignore x-data="{
                selectedVariantIndex: 0,
                selectedColorIndex: 0,
                product: null,
                currentVariant: null,
                currentColor: null,
                initialized: false,

                init() {
                    this.loadProductData();

                    if (!this.product) {
                        requestAnimationFrame(() => this.loadProductData());
                    }

                    if (!this.product) {
                        setTimeout(() => {
                            this.loadProductData();
                            if (!this.product) {
                                console.warn('[ProductVariantBlock] Failed to initialize after retries');
                            }
                        }, 250);
                    }
                },

                loadProductData() {
                    if (this.initialized) return;

                    try {
                        const dataEl = this.$el.querySelector('script[data-product]');
                        if (dataEl && dataEl.textContent) {
                            this.product = JSON.parse(dataEl.textContent.trim());

                            if (this.product?.variants?.length) {
                                this.currentVariant = this.product.variants[0];
                                this.currentColor = this.currentVariant?.colors?.[0] || null;
                                this.initialized = true;
                            }
                        }
                    } catch (e) {
                        console.error('[ProductVariantBlock] Parse error:', e);
                    }
                },

                switchVariant(index) {
                    if (!this.product?.variants?.[index]) return;

                    this.selectedVariantIndex = index;
                    this.selectedColorIndex = 0;
                    this.currentVariant = this.product.variants[index];
                    this.currentColor = this.currentVariant?.colors?.[0] || null;
                },

                selectColor(index) {
                    if (!this.currentVariant?.colors?.[index]) return;

                    this.selectedColorIndex = index;
                    this.currentColor = this.currentVariant.colors[index];
                },

                getFormattedPrice() {
                    const price = this.currentVariant?.price;
                    if (price === null || price === undefined || price === '') return null;
                    return typeof price === 'number' ? price.toLocaleString() : price;
                },

                shouldShowPriceSuffix() {
                    const price = this.currentVariant?.price;
                    if (price === null || price === undefined || price === '') return false;
                    return String(price).trim().toUpperCase() !== 'TBC';
                }
            }"
                x-effect="
                    if (product?.variants?.[selectedVariantIndex]) {
                        currentVariant = product.variants[selectedVariantIndex];
                        if (currentVariant?.colors?.[selectedColorIndex]) {
                            currentColor = currentVariant.colors[selectedColorIndex];
                        }
                    }
                "
                class="product-variant__alpine-root">

                {{-- Embedded product data as JSON for reliable parsing --}}
                <script type="application/json" data-product>@json($product)</script>

                <!-- Product Header -->
                <div class="product-variant__header mb-4 text-center">
                    <h1 class="h2 fw-bold mb-2">{{ $product['name'] ?? 'Product' }}</h1>
                    @if (!empty($product['common']))
                        <div class="product-variant__meta text-muted">
                            @if (!empty($product['common']['brand']))
                                <span class="me-3"><strong>Brand:</strong> {{ $product['common']['brand'] }}</span>
                            @endif
                            @if (!empty($product['common']['year']))
                                <span class="me-3"><strong>Year:</strong> {{ $product['common']['year'] }}</span>
                            @endif
                            @if (!empty($product['common']['body_type']))
                                <span class="me-3"><strong>Body Type:</strong>
                                    {{ $product['common']['body_type'] }}</span>
                            @endif
                            @if (!empty($product['common']['platform']))
                                <span class="me-3"><strong>Platform:</strong>
                                    {{ $product['common']['platform'] }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Variant Tabs -->
                <div class="product-variant__tabs mb-4">
                    <ul class="nav nav-tabs justify-content-center" role="tablist">
                        @foreach ($variants as $index => $variant)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                    :class="{ 'active': selectedVariantIndex === {{ $index }} }" type="button"
                                    @click.prevent="switchVariant({{ $index }})" role="tab"
                                    data-variant-index="{{ $index }}"
                                    :aria-selected="selectedVariantIndex === {{ $index }} ? 'true' : 'false'"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    {{ $variant['name'] ?? 'Variant ' . ($index + 1) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Variant Content -->
                <div class="row g-4">
                    <!-- Left Column: Images -->
                    <div class="col-lg-7">
                        {{-- SSR Fallback --}}
                        @if ($firstImage)
                            <div class="product-variant__main-image product-variant__main-image--ssr card mb-4"
                                x-show="!initialized">
                                <img src="{{ $firstImage }}" alt="{{ $firstColor['name'] ?? 'Product Image' }}"
                                    class="card-img-top"
                                    style="max-height: 500px; object-fit: contain;">
                            </div>
                        @endif

                        {{-- Alpine Dynamic Image --}}
                        <div class="product-variant__main-image card mb-4"
                            x-show="initialized && currentColor?.images?.length" x-cloak>
                            <img :src="currentColor?.images?.[0] || ''" :alt="currentColor?.name || 'Product Image'"
                                class="card-img-top product-variant__main-image-img"
                                style="max-height: 500px; object-fit: contain;">
                        </div>

                        {{-- SSR Fallback: Color Selection --}}
                        @if (!empty($firstVariant['colors']))
                            <div class="product-variant__color-selection product-variant__color-selection--ssr mb-4"
                                x-show="!initialized">
                                <h6 class="mb-3 fw-semibold">Select Color</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($firstVariant['colors'] as $colorIndex => $color)
                                        <button type="button"
                                            class="product-variant__color-option p-0 border-0 {{ $colorIndex === 0 ? 'active' : '' }}"
                                            style="width: 50px; height: 50px; border-radius: 50%; background: {{ $color['code'] ?? '#ccc' }}; outline: 3px solid {{ $colorIndex === 0 ? 'var(--tblr-primary)' : 'transparent' }}; outline-offset: -3px; position: relative; cursor: pointer;"
                                            title="{{ $color['name'] ?? 'Color ' . ($colorIndex + 1) }}"
                                            data-color-index="{{ $colorIndex }}">
                                            <i class="fas fa-check text-white position-absolute top-50 start-50 translate-middle"
                                                aria-hidden="true"
                                                style="{{ $colorIndex === 0 ? '' : 'display: none;' }}"></i>
                                        </button>
                                    @endforeach
                                </div>
                                <p class="mt-2 mb-0 text-muted">
                                    <strong>Selected:
                                    </strong><span>{{ $firstColor['name'] ?? 'Unnamed Color' }}</span>
                                </p>
                            </div>
                        @endif

                        {{-- Alpine Dynamic Color Selection --}}
                        <div class="product-variant__color-selection mb-4"
                            x-show="initialized && currentVariant?.colors?.length" x-cloak>
                            <h6 class="mb-3 fw-semibold">Select Color</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <template x-for="(color, colorIndex) in currentVariant?.colors || []"
                                    :key="colorIndex">
                                    <button type="button" class="product-variant__color-option p-0 border-0"
                                        :class="{ 'active': selectedColorIndex === colorIndex }"
                                        @click.prevent="selectColor(colorIndex)"
                                        :style="`width: 50px; height: 50px; border-radius: 50%; background: ${color.code || '#ccc'}; outline: 3px solid ${selectedColorIndex === colorIndex ? 'var(--tblr-primary)' : 'transparent'}; outline-offset: -3px; position: relative; cursor: pointer;`"
                                        :title="color.name || 'Color ' + (colorIndex + 1)">
                                        <i x-show="selectedColorIndex === colorIndex"
                                            class="fas fa-check text-white position-absolute top-50 start-50 translate-middle"></i>
                                    </button>
                                </template>
                            </div>
                            <p class="mt-2 mb-0 text-muted" x-show="currentColor">
                                <strong>Selected: </strong><span x-text="currentColor?.name || 'Unnamed Color'"></span>
                            </p>
                        </div>

                        {{-- Alpine Dynamic Additional Images --}}
                        <div class="product-variant__thumbnails mt-4"
                            x-show="initialized && currentColor?.images?.length > 1" x-cloak>
                            <h6 class="mb-3 fw-semibold">More Images</h6>
                            <div class="row g-2">
                                <template x-for="(image, imageIndex) in (currentColor?.images || []).slice(1)"
                                    :key="imageIndex">
                                    <div class="col-4 col-md-3">
                                        <img :src="image" :alt="'Product Image ' + (imageIndex + 2)"
                                            class="img-fluid rounded shadow-sm w-100 product-variant__thumbnail"
                                            style="height: 100px; object-fit: cover; cursor: pointer;"
                                            @click="$el.closest('.col-lg-7').querySelector('.product-variant__main-image-img').src = image">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Price & Specs -->
                    <div class="col-lg-5">
                        {{-- SSR Fallback: Variant Info --}}
                        @if ($firstVariant)
                            <div class="product-variant__info product-variant__info--ssr card mb-4"
                                x-show="!initialized">
                                <div class="card-body">
                                    <h2 class="card-title h3 fw-bold mb-2">{{ $firstVariant['name'] ?? 'Variant' }}</h2>
                                    @if (!empty($firstVariant['price']))
                                        @php
                                            $showPriceSuffix = strtoupper(trim((string) $firstVariant['price'])) !== 'TBC';
                                        @endphp
                                        <div class="product-variant__price mb-3">
                                            <span class="h4 fw-bold text-primary">
                                                ${{ is_numeric($firstVariant['price']) ? number_format($firstVariant['price']) : $firstVariant['price'] }}
                                                @if ($showPriceSuffix)
                                                    RRP. <small class="fs-6">+ORC</small>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    @if (!empty($firstVariant['promotion']) && $showPriceSuffix)
                                        <div class="product-variant__promotion text-danger">
                                            <strong>Launch Offer:</strong> {{ $firstVariant['promotion'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Alpine Dynamic Variant Info --}}
                        <div class="product-variant__info card mb-4" x-show="initialized" x-cloak>
                            <div class="card-body">
                                <h2 class="card-title h3 fw-bold mb-2" x-text="currentVariant?.name || 'Variant'"></h2>
                                <div class="product-variant__price mb-3" x-show="getFormattedPrice() !== null">
                                    <span class="h4 fw-bold text-primary">
                                        $<span x-text="getFormattedPrice()"></span>
                                        <span x-show="shouldShowPriceSuffix()"> RRP. <small
                                                class="fs-6">+ORC</small></span>
                                    </span>
                                </div>
                                <div class="product-variant__promotion text-danger"
                                    x-show="currentVariant?.promotion && shouldShowPriceSuffix()">
                                    <strong>Launch Offer:</strong> <span x-text="currentVariant?.promotion"></span>
                                </div>
                            </div>
                        </div>

                        {{-- SSR Fallback: Specifications --}}
                        @if (!empty($firstVariant['specs']))
                            <div class="product-variant__specs product-variant__specs--ssr card mb-4"
                                x-show="!initialized">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Specifications</h5>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach ($firstVariant['specs'] as $spec)
                                        <div class="list-group-item">{{ $spec }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Alpine Dynamic Specifications --}}
                        <div class="product-variant__specs card mb-4"
                            x-show="initialized && currentVariant?.specs?.length" x-cloak>
                            <div class="card-header">
                                <h5 class="card-title mb-0">Specifications</h5>
                            </div>
                            <div class="list-group list-group-flush">
                                <template x-for="(spec, specIndex) in currentVariant?.specs || []"
                                    :key="specIndex">
                                    <div class="list-group-item" x-text="spec"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
