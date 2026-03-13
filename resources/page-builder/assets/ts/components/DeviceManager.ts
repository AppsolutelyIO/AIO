// Device Manager - Handles device preview functionality
export class DeviceManager {
    private currentDevice: string = 'desktop';

    constructor() {
        this.initializeDeviceButtons();
    }

    private initializeDeviceButtons(): void {
        const deviceButtons = document.querySelectorAll<HTMLButtonElement>('.device-btn');

        deviceButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                const target = e.currentTarget as HTMLButtonElement;
                const device = target.dataset.device;

                if (device) {
                    this.setActiveDevice(device);
                    this.updateDevicePreview(device);
                }
            });
        });
    }

    private setActiveDevice(device: string): void {
        this.currentDevice = device;

        // Update button states and aria-pressed
        document.querySelectorAll<HTMLButtonElement>('.device-btn').forEach((btn) => {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', btn.dataset.device === device ? 'true' : 'false');
        });

        const activeButton = document.querySelector(`[data-device="${device}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
        }
    }

    private updateDevicePreview(device: string): void {
        const canvas = document.getElementById('editor-canvas');
        if (!canvas) return;

        // Remove existing device classes
        canvas.classList.remove('device-desktop', 'device-tablet', 'device-mobile');

        // Add new device class
        canvas.classList.add(`device-${device}`);

        // Apply device-specific styles
        this.applyDeviceStyles(device);
    }

    private applyDeviceStyles(device: string): void {
        const canvas = document.getElementById('editor-canvas');
        if (!canvas) return;

        switch (device) {
            case 'mobile':
                canvas.style.maxWidth = '375px';
                canvas.style.margin = '0 auto';
                break;
            case 'tablet':
                canvas.style.maxWidth = '768px';
                canvas.style.margin = '0 auto';
                break;
            case 'desktop':
            default:
                canvas.style.maxWidth = '100%';
                canvas.style.margin = '0';
                break;
        }
    }

    public getCurrentDevice(): string {
        return this.currentDevice;
    }

    public getDeviceDimensions(device: string): { width: number; height: number } {
        const dimensions = {
            desktop: { width: 1200, height: 800 },
            tablet: { width: 768, height: 1024 },
            mobile: { width: 375, height: 667 },
        };

        return dimensions[device as keyof typeof dimensions] || dimensions.desktop;
    }

    public isMobile(): boolean {
        return this.currentDevice === 'mobile';
    }

    public isTablet(): boolean {
        return this.currentDevice === 'tablet';
    }

    public isDesktop(): boolean {
        return this.currentDevice === 'desktop';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DeviceManager();
});
