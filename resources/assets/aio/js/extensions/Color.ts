
interface ColorInstance extends Record<string, unknown> {
    get(color: string): string;
    lighten(color: string, amt: number): string;
    darken(color: string, amt: number): string;
    alpha(color: string, alpha: number): string;
    toRBG(color: string, amt?: number): [number, number, number];
    all(): Record<string, string>;
}

export default class Color {
    constructor(AIO: AIOInstance) {
        let colors = (AIO.config.colors || {}) as Record<string, string>,
            newInstance = $.extend(colors) as unknown as ColorInstance,
            _this = this;

        newInstance.get = function (color: string): string {
            return colors[color] || color;
        };

        newInstance.lighten = function (color: string, amt: number): string {
            return _this.lighten(newInstance.get(color), amt)
        };

        newInstance.darken = (color: string, amt: number): string => {
            return newInstance.lighten(color, -amt)
        };

        newInstance.alpha = (color: string, alpha: number): string => {
            let results = newInstance.toRBG(color);

            return `rgba(${results[0]}, ${results[1]}, ${results[2]}, ${alpha})`;
        };

        newInstance.toRBG = (color: string, amt?: number): [number, number, number] => {
            if (color.indexOf('#') === 0) {
                color = color.slice(1);
            }

            return _this.toRBG(newInstance.get(color), amt);
        };

        newInstance.all = function (): Record<string, string> {
            return colors;
        };

        AIO.color = newInstance as unknown as Record<string, unknown>;
    }

    lighten(color: string, amt: number): string {
        let hasPrefix = false;

        if (color.indexOf('#') === 0) {
            color = color.slice(1);

            hasPrefix = true;
        }

        let colors = this.toRBG(color, amt);

        return (hasPrefix ? '#' : '') + (colors[2] | (colors[1] << 8) | (colors[0] << 16)).toString(16);
    }

    toRBG(color: string, amt?: number): [number, number, number] {
        let format = (value: number): number => {
            if (value > 255) {
                return 255;
            }
            if (value < 0) {
                return 0;
            }

            return value;
        };

        amt = amt || 0;

        let num = parseInt(color, 16),
            red = format((num >> 16) + amt),
            blue = format(((num >> 8) & 0x00FF) + amt),
            green = format((num & 0x0000FF) + amt);

        return [red, blue, green]
    }
}
