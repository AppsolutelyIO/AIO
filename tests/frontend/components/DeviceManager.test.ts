/**
 * TDD Tests for DeviceManager component
 * Tests for getCurrentDevice, getDeviceDimensions, isMobile, isTablet, isDesktop
 */

import { DeviceManager } from '@resources/page-builder/assets/ts/components/DeviceManager';
import { beforeEach, describe, expect, it } from 'vitest';

describe('DeviceManager', () => {
    let manager: DeviceManager;

    beforeEach(() => {
        document.body.innerHTML = `
            <div id="editor-canvas"></div>
            <button class="device-btn" data-device="desktop">Desktop</button>
            <button class="device-btn" data-device="tablet">Tablet</button>
            <button class="device-btn" data-device="mobile">Mobile</button>
        `;
        manager = new DeviceManager();
    });

    describe('getCurrentDevice', () => {
        it('should return "desktop" by default', () => {
            expect(manager.getCurrentDevice()).toBe('desktop');
        });

        it('should return the active device after clicking mobile button', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            mobileBtn.click();
            expect(manager.getCurrentDevice()).toBe('mobile');
        });

        it('should return the active device after clicking tablet button', () => {
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;
            tabletBtn.click();
            expect(manager.getCurrentDevice()).toBe('tablet');
        });

        it('should update when switching between devices', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            const desktopBtn = document.querySelector('[data-device="desktop"]') as HTMLButtonElement;

            mobileBtn.click();
            expect(manager.getCurrentDevice()).toBe('mobile');

            desktopBtn.click();
            expect(manager.getCurrentDevice()).toBe('desktop');
        });
    });

    describe('getDeviceDimensions', () => {
        it('should return desktop dimensions: 1200x800', () => {
            const dims = manager.getDeviceDimensions('desktop');
            expect(dims).toEqual({ width: 1200, height: 800 });
        });

        it('should return tablet dimensions: 768x1024', () => {
            const dims = manager.getDeviceDimensions('tablet');
            expect(dims).toEqual({ width: 768, height: 1024 });
        });

        it('should return mobile dimensions: 375x667', () => {
            const dims = manager.getDeviceDimensions('mobile');
            expect(dims).toEqual({ width: 375, height: 667 });
        });

        it('should fall back to desktop dimensions for unknown device', () => {
            const dims = manager.getDeviceDimensions('unknown-device');
            expect(dims).toEqual({ width: 1200, height: 800 });
        });
    });

    describe('isMobile', () => {
        it('should return false by default (desktop)', () => {
            expect(manager.isMobile()).toBe(false);
        });

        it('should return true when mobile is active', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            mobileBtn.click();
            expect(manager.isMobile()).toBe(true);
        });

        it('should return false when tablet is active', () => {
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;
            tabletBtn.click();
            expect(manager.isMobile()).toBe(false);
        });
    });

    describe('isTablet', () => {
        it('should return false by default (desktop)', () => {
            expect(manager.isTablet()).toBe(false);
        });

        it('should return true when tablet is active', () => {
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;
            tabletBtn.click();
            expect(manager.isTablet()).toBe(true);
        });

        it('should return false when mobile is active', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            mobileBtn.click();
            expect(manager.isTablet()).toBe(false);
        });
    });

    describe('isDesktop', () => {
        it('should return true by default', () => {
            expect(manager.isDesktop()).toBe(true);
        });

        it('should return false when mobile is active', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            mobileBtn.click();
            expect(manager.isDesktop()).toBe(false);
        });

        it('should return false when tablet is active', () => {
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;
            tabletBtn.click();
            expect(manager.isDesktop()).toBe(false);
        });

        it('should return true after switching back to desktop', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            const desktopBtn = document.querySelector('[data-device="desktop"]') as HTMLButtonElement;

            mobileBtn.click();
            expect(manager.isDesktop()).toBe(false);

            desktopBtn.click();
            expect(manager.isDesktop()).toBe(true);
        });
    });

    describe('device button interactions', () => {
        it('should set active class on clicked device button', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            mobileBtn.click();
            expect(mobileBtn.classList.contains('active')).toBe(true);
        });

        it('should remove active class from previously active button', () => {
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;

            tabletBtn.click();
            expect(tabletBtn.classList.contains('active')).toBe(true);

            mobileBtn.click();
            expect(tabletBtn.classList.contains('active')).toBe(false);
            expect(mobileBtn.classList.contains('active')).toBe(true);
        });

        it('should update aria-pressed on device buttons', () => {
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;
            const desktopBtn = document.querySelector('[data-device="desktop"]') as HTMLButtonElement;

            mobileBtn.click();
            expect(mobileBtn.getAttribute('aria-pressed')).toBe('true');
            expect(desktopBtn.getAttribute('aria-pressed')).toBe('false');
        });

        it('should update canvas class when device changes', () => {
            const canvas = document.getElementById('editor-canvas') as HTMLElement;
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;

            mobileBtn.click();
            expect(canvas.classList.contains('device-mobile')).toBe(true);
            expect(canvas.classList.contains('device-desktop')).toBe(false);
        });

        it('should apply mobile max-width styles to canvas', () => {
            const canvas = document.getElementById('editor-canvas') as HTMLElement;
            const mobileBtn = document.querySelector('[data-device="mobile"]') as HTMLButtonElement;

            mobileBtn.click();
            expect(canvas.style.maxWidth).toBe('375px');
        });

        it('should apply tablet max-width styles to canvas', () => {
            const canvas = document.getElementById('editor-canvas') as HTMLElement;
            const tabletBtn = document.querySelector('[data-device="tablet"]') as HTMLButtonElement;

            tabletBtn.click();
            expect(canvas.style.maxWidth).toBe('768px');
        });

        it('should apply desktop max-width styles to canvas', () => {
            const canvas = document.getElementById('editor-canvas') as HTMLElement;
            const desktopBtn = document.querySelector('[data-device="desktop"]') as HTMLButtonElement;

            desktopBtn.click();
            expect(canvas.style.maxWidth).toBe('100%');
        });
    });
});
