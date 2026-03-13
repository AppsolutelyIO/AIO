
interface DarkModeOptions {
    sidebar_dark: boolean;
    dark_mode: boolean;
    class: {
        dark: string;
        sidebarLight: string;
        sidebarDark: string;
    };
}

export default class DarkMode {
    public options: DarkModeOptions;

    constructor(AIO: AIOInstance) {
        this.options = {
            sidebar_dark: AIO.config.sidebar_dark as boolean,
            dark_mode: AIO.config.dark_mode as boolean,
            class: {
                dark: 'dark-mode',
                sidebarLight: (AIO.config.sidebar_light_style as string) || 'sidebar-light-primary',
                sidebarDark: 'sidebar-dark-white',
            }
        };

        AIO.darkMode = this as unknown as Record<string, unknown>;
    }

    initSwitcher(selector: string): void {
        var storage = localStorage || {setItem: function (): void {}, getItem: function (): string | null { return null; }},
            darkMode = this,
            key = 'aio-theme-mode',
            icon = '.dark-mode-switcher i';

        function switchMode(theme: string | null): void {
            switch (theme) {
                case 'dark': {
                    $(icon).addClass('icon-sun').removeClass('icon-moon');
                    darkMode.display(true);
                    break;
                }
                case 'def': {
                    darkMode.display(false);
                    $(icon).removeClass('icon-sun').addClass('icon-moon');
                    break;
                }
                default: {
                    break;
                }
            }
        }

        switchMode(storage.getItem(key));

        $(document).off('click', selector).on('click', selector, function () {
            $(icon).toggleClass('icon-sun icon-moon');

            if ($(icon).hasClass('icon-moon')) {
                switchMode('def');

                storage.setItem(key, 'def');

            } else {
                storage.setItem(key, 'dark');

                switchMode('dark')
            }
        })

        window.addEventListener('storage', function (event: StorageEvent) {
            if (event.key === key) {
                switchMode(event.newValue);
            }
        });
    }

    toggle(): void {
        if ($('body').hasClass(this.options.class.dark)) {
            this.display(false)
        } else {
            this.display(true)
        }
    }

    display(show: boolean): void {
        let $document = $(document),
            $body = $('body'),
            $sidebar = $('.main-menu .main-sidebar'),
            options = this.options,
            cls = options.class;

        if (show) {
            $body.addClass(cls.dark);
            $sidebar.removeClass(cls.sidebarLight).addClass(cls.sidebarDark);

            $document.trigger('dark-mode.shown');

            return;
        }

        $body.removeClass(cls.dark);
        if (! options.sidebar_dark) {
            $sidebar.addClass(cls.sidebarLight).removeClass(cls.sidebarDark);
        }

        $document.trigger('dark-mode.hide');
    }
}
