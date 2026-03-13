
/* @see https://github.com/lodash/lodash/blob/master/debounce.js */
/* @see https://www.lodashjs.com/docs/lodash.debounce */

interface DebounceOptions {
    leading?: boolean;
    maxWait?: number;
    trailing?: boolean;
}

interface DebouncedFunction {
    (...args: unknown[]): unknown;
    cancel(): void;
    flush(): unknown;
    pending(): boolean;
}

function debounce(func: (...args: unknown[]) => unknown, wait: number, options?: DebounceOptions): DebouncedFunction {
    var lastArgs: IArguments | undefined,
        lastThis: unknown,
        maxWait: number,
        result: unknown,
        timerId: ReturnType<typeof setTimeout> | undefined,
        lastCallTime: number | undefined;

    var lastInvokeTime = 0;
    var leading = false;
    var maxing = false;
    var trailing = true;

    if (typeof func !== 'function') {
        throw new TypeError('Expected a function')
    }
    wait = +wait || 0;
    if (isObject(options)) {
        leading = !!options!.leading;
        maxing = 'maxWait' in options!;
        maxWait = maxing ? Math.max(+options!.maxWait! || 0, wait) : wait;
        trailing = 'trailing' in options! ? !!options!.trailing : trailing
    }

    function isObject(value: unknown): boolean {
        var type = typeof value;
        return value != null && (type === 'object' || type === 'function')
    }


    function invokeFunc(time: number): unknown {
        var args = lastArgs;
        var thisArg = lastThis;

        lastArgs = lastThis = undefined;
        lastInvokeTime = time;
        result = func.apply(thisArg as unknown, args as unknown as unknown[]);
        return result
    }

    function startTimer(pendingFunc: () => void, waitMs: number): ReturnType<typeof setTimeout> {
        return setTimeout(pendingFunc, waitMs)
    }

    function cancelTimer(id: ReturnType<typeof setTimeout>): void {
        clearTimeout(id)
    }

    function leadingEdge(time: number): unknown {
        lastInvokeTime = time;
        timerId = startTimer(timerExpired, wait);
        return leading ? invokeFunc(time) : result
    }

    function remainingWait(time: number): number {
        var timeSinceLastCall = time - (lastCallTime as number);
        var timeSinceLastInvoke = time - lastInvokeTime;
        var timeWaiting = wait - timeSinceLastCall;

        return maxing
            ? Math.min(timeWaiting, maxWait - timeSinceLastInvoke)
            : timeWaiting
    }

    function shouldInvoke(time: number): boolean {
        var timeSinceLastCall = time - (lastCallTime as number);
        var timeSinceLastInvoke = time - lastInvokeTime;

        return (lastCallTime === undefined || (timeSinceLastCall >= wait) ||
            (timeSinceLastCall < 0) || (maxing && timeSinceLastInvoke >= maxWait))
    }

    function timerExpired(): unknown {
        var time = Date.now();
        if (shouldInvoke(time)) {
            return trailingEdge(time)
        }
        timerId = startTimer(timerExpired, remainingWait(time))
    }

    function trailingEdge(time: number): unknown {
        timerId = undefined;

        if (trailing && lastArgs) {
            return invokeFunc(time)
        }
        lastArgs = lastThis = undefined;
        return result
    }

    function cancel(): void {
        if (timerId !== undefined) {
            cancelTimer(timerId)
        }
        lastInvokeTime = 0;
        lastArgs = lastCallTime = lastThis = timerId = undefined
    }

    function flush(): unknown {
        return timerId === undefined ? result : trailingEdge(Date.now())
    }

    function pending(): boolean {
        return timerId !== undefined
    }

    function debounced(this: unknown): unknown {
        var time = Date.now();
        var isInvoking = shouldInvoke(time);

        lastArgs = arguments;
        lastThis = this;
        lastCallTime = time;

        if (isInvoking) {
            if (timerId === undefined) {
                return leadingEdge(lastCallTime)
            }
            if (maxing) {
                timerId = startTimer(timerExpired, wait);
                return invokeFunc(lastCallTime)
            }
        }
        if (timerId === undefined) {
            timerId = startTimer(timerExpired, wait)
        }
        return result
    }
    debounced.cancel = cancel;
    debounced.flush = flush;
    debounced.pending = pending;
    return debounced
}

export default debounce
