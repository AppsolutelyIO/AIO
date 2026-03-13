/**
 * Video Showcase Component
 * Handles video autoplay, mobile compatibility, and performance optimization
 */

class VideoShowcase {
    private video: HTMLVideoElement | null;
    private observer: IntersectionObserver | null;

    constructor() {
        this.video = null;
        this.observer = null;
    }

    init(): void {
        this.video = document.querySelector<HTMLVideoElement>('.video-showcase video');
        if (this.video) {
            this.setupVideo();
            this.setupIntersectionObserver();
            this.handleVideoEvents();
        }
    }

    setupVideo(): void {
        if (!this.video) return;

        this.video.setAttribute('playsinline', '');
        this.video.setAttribute('webkit-playsinline', '');
        this.video.setAttribute('preload', 'metadata');
    }

    handleVideoEvents(): void {
        if (!this.video) return;

        this.video.addEventListener('loadeddata', () => {
            if (this.video && this.video.readyState >= 3) {
                this.playVideo();
            }
        });

        this.video.addEventListener('canplaythrough', () => {
            this.playVideo();
        });

        this.video.addEventListener('error', () => {
            this.handleVideoError();
        });
    }

    playVideo(): void {
        if (!this.video) return;

        const playPromise = this.video.play();

        if (playPromise !== undefined) {
            playPromise.catch(() => {
                this.handleAutoplayBlocked();
            });
        }
    }

    handleAutoplayBlocked(): void {
        document.addEventListener(
            'click',
            () => {
                this.video?.play().catch(() => {});
            },
            { once: true }
        );
    }

    handleVideoError(): void {
        const fallbackImage = document.querySelector<HTMLImageElement>(
            '.video-showcase .video-showcase__mobile-fallback img'
        );
        if (fallbackImage && this.video) {
            fallbackImage.style.display = 'block';
            this.video.style.display = 'none';
        }
    }

    setupIntersectionObserver(): void {
        if (!this.video) return;

        this.observer = new IntersectionObserver(
            (entries: IntersectionObserverEntry[]) => {
                entries.forEach((entry: IntersectionObserverEntry) => {
                    if (entry.isIntersecting) {
                        this.video?.play().catch(() => {});
                    } else {
                        this.video?.pause();
                    }
                });
            },
            {
                threshold: 0.5,
                rootMargin: '50px',
            }
        );

        this.observer.observe(this.video);
    }

    destroy(): void {
        if (this.observer) {
            this.observer.disconnect();
        }

        if (this.video) {
            this.video.pause();
            this.video.removeAttribute('src');
            this.video.load();
        }
    }
}

let videoShowcaseInstance: VideoShowcase | null = null;

function initVideoShowcase(): void {
    videoShowcaseInstance?.destroy();
    videoShowcaseInstance = new VideoShowcase();
    videoShowcaseInstance.init();
}

export function init(): void {
    initVideoShowcase();
}
export default VideoShowcase;
