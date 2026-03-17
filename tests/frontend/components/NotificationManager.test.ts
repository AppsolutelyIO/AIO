/**
 * TDD Tests for NotificationManager component
 * Tests for show, clearAll, success, error, warning methods
 */

import { NotificationManager } from '@resources/page-builder/assets/ts/components/NotificationManager';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

describe('NotificationManager', () => {
    let manager: NotificationManager;

    beforeEach(() => {
        document.body.innerHTML = '';
        vi.useFakeTimers();
        manager = new NotificationManager();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    describe('constructor / setupNotificationContainer', () => {
        it('should create a notification container in the DOM', () => {
            const container = document.getElementById('notification-container');
            expect(container).not.toBeNull();
        });

        it('should not create duplicate containers when instantiated multiple times', () => {
            new NotificationManager();
            new NotificationManager();
            const containers = document.querySelectorAll('#notification-container');
            expect(containers.length).toBe(1);
        });

        it('should set the container position to fixed', () => {
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.style.position).toBe('fixed');
        });
    });

    describe('show', () => {
        it('should add a notification element to the container', () => {
            manager.show('Test message', 'success', 5000);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(1);
        });

        it('should display the given message', () => {
            manager.show('Hello World', 'success', 5000);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.textContent).toContain('Hello World');
        });

        it('should apply the correct CSS class for success type', () => {
            manager.show('OK', 'success', 5000);
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('success')).toBe(true);
        });

        it('should apply the correct CSS class for error type', () => {
            manager.show('Error!', 'error', 5000);
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('error')).toBe(true);
        });

        it('should apply the correct CSS class for warning type', () => {
            manager.show('Warning!', 'warning', 5000);
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('warning')).toBe(true);
        });

        it('should include a close button in the notification', () => {
            manager.show('Test', 'success', 5000);
            const closeBtn = document.querySelector('.notification button') as HTMLButtonElement;
            expect(closeBtn).not.toBeNull();
        });

        it('should auto-remove the notification after the given duration', () => {
            manager.show('Auto-remove', 'success', 1000);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(1);

            // After duration, the remove is triggered
            vi.advanceTimersByTime(1000);
            // After remove animation (300ms)
            vi.advanceTimersByTime(300);
            expect(container.children.length).toBe(0);
        });

        it('should default to success type when no type is provided', () => {
            manager.show('Default type');
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('success')).toBe(true);
        });

        it('should add multiple notifications', () => {
            manager.show('First', 'success', 5000);
            manager.show('Second', 'error', 5000);
            manager.show('Third', 'warning', 5000);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(3);
        });
    });

    describe('clearAll', () => {
        it('should remove all notifications from the container', () => {
            manager.show('A', 'success', 10000);
            manager.show('B', 'error', 10000);
            manager.show('C', 'warning', 10000);

            manager.clearAll();
            vi.advanceTimersByTime(300);

            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(0);
        });

        it('should not throw when there are no notifications', () => {
            expect(() => manager.clearAll()).not.toThrow();
        });
    });

    describe('success', () => {
        it('should show a notification with success type', () => {
            manager.success('Operation successful');
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('success')).toBe(true);
            expect(notification.textContent).toContain('Operation successful');
        });

        it('should accept an optional duration', () => {
            manager.success('Done', 2000);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(1);

            vi.advanceTimersByTime(2000);
            vi.advanceTimersByTime(300);
            expect(container.children.length).toBe(0);
        });
    });

    describe('error', () => {
        it('should show a notification with error type', () => {
            manager.error('Something went wrong');
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('error')).toBe(true);
            expect(notification.textContent).toContain('Something went wrong');
        });

        it('should accept an optional duration', () => {
            manager.error('Failed', 1500);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(1);

            vi.advanceTimersByTime(1500);
            vi.advanceTimersByTime(300);
            expect(container.children.length).toBe(0);
        });
    });

    describe('warning', () => {
        it('should show a notification with warning type', () => {
            manager.warning('Watch out');
            const notification = document.querySelector('.notification') as HTMLElement;
            expect(notification.classList.contains('warning')).toBe(true);
            expect(notification.textContent).toContain('Watch out');
        });

        it('should accept an optional duration', () => {
            manager.warning('Caution', 2500);
            const container = document.getElementById('notification-container') as HTMLElement;
            expect(container.children.length).toBe(1);

            vi.advanceTimersByTime(2500);
            vi.advanceTimersByTime(300);
            expect(container.children.length).toBe(0);
        });
    });
});
