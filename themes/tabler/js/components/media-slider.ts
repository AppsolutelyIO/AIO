/**
 * Media Slider Carousel Component
 * Uses Swiper.js for carousel functionality
 */

import Swiper from 'swiper/bundle';
import type { Swiper as SwiperType, SwiperOptions } from 'swiper/types';

class MediaSliderCarousel {
    private swipers: Map<string, SwiperType>;

    constructor() {
        this.swipers = new Map();
        this.init();
    }

    init(): void {
        // DOMContentLoaded and livewire:navigated are handled by init.ts
        // livewire:updated is handled at module level to avoid duplicate listeners
    }

    initializeSliders(): void {
        // Find all media slider carousels
        const sliders = document.querySelectorAll<HTMLElement>('.swiper[data-slider-id]');

        sliders.forEach((sliderElement: HTMLElement) => {
            const sliderId = sliderElement.dataset.sliderId;

            if (!sliderId) return;

            // Skip if already initialized
            if (this.swipers.has(sliderId)) {
                return;
            }

            // Store reference to this instance for callbacks
            const self = this;

            // Detect slider type
            const isSimpleSlider = sliderElement.closest('.media-slider--default') !== null;

            // Base Swiper configuration
            const swiperConfig: SwiperOptions = {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                keyboard: {
                    enabled: true,
                },
                on: {
                    init: function (this: SwiperType) {
                        // Only update title slider if it exists
                        if (self.hasTitleSlider(sliderId)) {
                            self.updateTitleSlider(sliderId, 0);
                        }
                    },
                    slideChange: function (this: SwiperType) {
                        self.pauseVideosInSlider(sliderElement);
                        if (self.hasTitleSlider(sliderId)) {
                            self.updateTitleSlider(sliderId, this.realIndex);
                        }
                    },
                },
            };

            // Add type-specific configuration
            if (isSimpleSlider) {
                // Simple slider configuration
                Object.assign(swiperConfig, {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    navigation: {
                        nextEl: `.swiper-button-next[data-slider-id="${sliderId}"]`,
                        prevEl: `.swiper-button-prev[data-slider-id="${sliderId}"]`,
                        addIcons: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                });
            } else {
                // Carousel slider configuration
                Object.assign(swiperConfig, {
                    slidesPerView: 'auto',
                    spaceBetween: 30,
                    navigation: {
                        nextEl: `.swiper-button-next[data-slider-id="${sliderId}"]`,
                        prevEl: `.swiper-button-prev[data-slider-id="${sliderId}"]`,
                        addIcons: false,
                    },
                    breakpoints: {
                        320: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        768: {
                            slidesPerView: 'auto',
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 'auto',
                            spaceBetween: 30,
                        },
                    },
                });
            }

            // Initialize Swiper with element reference (avoids selector injection)
            const swiper = new Swiper(sliderElement, swiperConfig);

            // Store swiper instance
            this.swipers.set(sliderId, swiper);
        });
    }

    hasTitleSlider(sliderId: string): boolean {
        try {
            const sliderElement = document.querySelector(`[data-slider-id="${sliderId}"]`);
            if (!sliderElement) return false;

            const container = sliderElement.closest('.media-slider--carousel');
            if (!container) return false;

            const titleSlider = container.querySelector('.media-slider__title-slider');
            return titleSlider !== null;
        } catch (error) {
            console.warn('Error checking for title slider:', error);
            return false;
        }
    }

    updateTitleSlider(sliderId: string, slideIndex: number): void {
        try {
            const sliderElement = document.querySelector(`[data-slider-id="${sliderId}"]`);
            if (!sliderElement) return;

            const container = sliderElement.closest('.media-slider--carousel');
            if (!container) return;

            const titleSlider = container.querySelector('.media-slider__title-slider');
            if (!titleSlider) return;

            const titleSlides = titleSlider.querySelectorAll<HTMLElement>('.media-slider__title-slide');
            const totalSlides = titleSlides.length;

            if (totalSlides === 0) return;

            // Remove active class from all slides
            titleSlides.forEach((slide: HTMLElement) => {
                slide.classList.remove('active');
            });

            // Add active class to current slide
            const currentIndex = slideIndex % totalSlides;
            if (titleSlides[currentIndex]) {
                titleSlides[currentIndex].classList.add('active');
            }
        } catch (error) {
            console.warn('Error updating title slider:', error);
        }
    }

    /**
     * Pause all videos within a specific slider (single handler, no per-video listeners)
     */
    private pauseVideosInSlider(sliderElement: HTMLElement): void {
        sliderElement.querySelectorAll<HTMLVideoElement>('.swiper-slide video').forEach((video) => video.pause());
    }

    // Destroy specific slider
    destroySlider(sliderId: string): void {
        if (this.swipers.has(sliderId)) {
            this.swipers.get(sliderId)?.destroy(true, true);
            this.swipers.delete(sliderId);
        }
    }

    // Destroy all sliders
    destroyAll(): void {
        this.swipers.forEach((swiper: SwiperType) => {
            swiper.destroy(true, true);
        });
        this.swipers.clear();
    }
}

let mediaSliderInstance: MediaSliderCarousel | null = null;

export function init(): void {
    mediaSliderInstance?.destroyAll();
    mediaSliderInstance = new MediaSliderCarousel();
    mediaSliderInstance.initializeSliders();
}

document.addEventListener('livewire:updated', () => {
    mediaSliderInstance?.initializeSliders();
});

// Export for potential external use
window.MediaSliderCarousel = MediaSliderCarousel;
