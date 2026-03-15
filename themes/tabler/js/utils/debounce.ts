/**
 * Debounce utility
 * Delays invoking a function until after wait milliseconds have elapsed
 * since the last time it was invoked.
 */
export function debounce<T extends (...args: unknown[]) => void>(
    fn: T,
    wait: number,
): (...args: Parameters<T>) => void {
    let timeoutId: ReturnType<typeof setTimeout> | null = null;

    return (...args: Parameters<T>) => {
        if (timeoutId !== null) {
            clearTimeout(timeoutId);
        }
        timeoutId = setTimeout(() => {
            timeoutId = null;
            fn(...args);
        }, wait);
    };
}
