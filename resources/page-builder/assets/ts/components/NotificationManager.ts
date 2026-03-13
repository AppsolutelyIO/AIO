// Notification Manager - Handles notifications and alerts
export class NotificationManager {
    private notifications: HTMLElement[] = [];

    constructor() {
        this.setupNotificationContainer();
    }

    private setupNotificationContainer(): void {
        // Create notification container if it doesn't exist
        let container = document.getElementById('notification-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-container';
            container.style.cssText = `
        position: fixed;
        top: 5rem;
        right: 1rem;
        z-index: 9999;
        pointer-events: none;
      `;
            document.body.appendChild(container);
        }
    }

    public show(message: string, type: 'success' | 'error' | 'warning' = 'success', duration: number = 3000): void {
        const notification = this.createNotification(message, type);
        const container = document.getElementById('notification-container');

        if (container) {
            container.appendChild(notification);
            this.notifications.push(notification);

            // Auto remove after duration
            setTimeout(() => {
                this.remove(notification);
            }, duration);

            // Add click to dismiss
            notification.addEventListener('click', () => {
                this.remove(notification);
            });
        }
    }

    private createNotification(message: string, type: string): HTMLElement {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
      background: none;
      border: none;
      color: inherit;
      font-size: 1.2rem;
      cursor: pointer;
      margin-left: 0.5rem;
      padding: 0;
    `;

        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.remove(notification);
        });

        notification.appendChild(closeBtn);

        return notification;
    }

    private remove(notification: HTMLElement): void {
        notification.style.animation = 'slideOut 0.3s ease-in forwards';

        setTimeout(() => {
            notification.remove();
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        }, 300);
    }

    public clearAll(): void {
        this.notifications.forEach((notification) => {
            this.remove(notification);
        });
    }

    // Convenience methods
    public success(message: string, duration?: number): void {
        this.show(message, 'success', duration);
    }

    public error(message: string, duration?: number): void {
        this.show(message, 'error', duration);
    }

    public warning(message: string, duration?: number): void {
        this.show(message, 'warning', duration);
    }
}

// Export singleton instance
export const notificationManager = new NotificationManager();
