/**
 * --------------------------------------------
 * AdminLTE Layout.ts
 * License MIT
 * --------------------------------------------
 */

interface LayoutConfig {
  scrollbarTheme: string
  scrollbarAutoHide: string
  panelAutoHeight: boolean | number
  loginRegisterAutoHeight: boolean | number
}

const Layout = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'Layout'
  const DATA_KEY           = 'lte.layout'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]

  const Event = {
    SIDEBAR: 'sidebar'
  }

  const Selector = {
    HEADER         : '.main-header',
    MAIN_SIDEBAR   : '.main-sidebar',
    SIDEBAR        : '.main-sidebar .sidebar',
    CONTENT        : '.content-wrapper',
    BRAND          : '.brand-link',
    CONTENT_HEADER : '.content-header',
    WRAPPER        : '.wrapper',
    CONTROL_SIDEBAR: '.control-sidebar',
    CONTROL_SIDEBAR_CONTENT: '.control-sidebar-content',
    CONTROL_SIDEBAR_BTN: '[data-widget="control-sidebar"]',
    LAYOUT_FIXED   : '.layout-fixed',
    FOOTER         : '.main-footer',
    PUSHMENU_BTN   : '[data-widget="pushmenu"]',
    LOGIN_BOX      : '.login-box',
    REGISTER_BOX   : '.register-box'
  }

  const ClassName = {
    HOLD           : 'hold-transition',
    SIDEBAR        : 'main-sidebar',
    CONTENT_FIXED  : 'content-fixed',
    SIDEBAR_FOCUSED: 'sidebar-focused',
    LAYOUT_FIXED   : 'layout-fixed',
    NAVBAR_FIXED   : 'layout-navbar-fixed',
    FOOTER_FIXED   : 'layout-footer-fixed',
    LOGIN_PAGE     : 'login-page',
    REGISTER_PAGE  : 'register-page',
    CONTROL_SIDEBAR_SLIDE_OPEN: 'control-sidebar-slide-open',
    CONTROL_SIDEBAR_OPEN: 'control-sidebar-open',
  }

  const Default: LayoutConfig = {
    scrollbarTheme : 'os-theme-light',
    scrollbarAutoHide: 'l',
    panelAutoHeight: true,
    loginRegisterAutoHeight: true,
  }

  /**
   * Class Definition
   * ====================================================
   */

  class Layout {
    private _config: LayoutConfig
    private _element: JQuery

    constructor(element: JQuery, config: LayoutConfig) {
      this._config  = config
      this._element = element

      this._init()
    }

    // Public

    fixLayoutHeight(extra: string | null = null): void {
      let control_sidebar = 0

      if ($('body').hasClass(ClassName.CONTROL_SIDEBAR_SLIDE_OPEN) || $('body').hasClass(ClassName.CONTROL_SIDEBAR_OPEN) || extra == 'control_sidebar') {
        control_sidebar = $(Selector.CONTROL_SIDEBAR_CONTENT).height() as number
      }

      const heights: Record<string, number> = {
        window: $(window).height() as number,
        header: $(Selector.HEADER).length !== 0 ? $(Selector.HEADER).outerHeight() as number : 0,
        footer: $(Selector.FOOTER).length !== 0 ? $(Selector.FOOTER).outerHeight() as number : 0,
        sidebar: $(Selector.SIDEBAR).length !== 0 ? $(Selector.SIDEBAR).height() as number : 0,
        control_sidebar: control_sidebar,
      }

      const max = this._max(heights)
      let offset: number | boolean = this._config.panelAutoHeight

      if (offset === true) {
        offset = 0;
      }

      if (offset !== false) {
        if (max == heights.control_sidebar) {
          $(Selector.CONTENT).css('min-height', ((max as number) + (offset as number)))
        } else if (max == heights.window) {
          $(Selector.CONTENT).css('min-height', ((max as number) + (offset as number)) - heights.header - heights.footer)
        } else {
          $(Selector.CONTENT).css('min-height', ((max as number) + (offset as number)) - heights.header)
        }
      }

      if ($('body').hasClass(ClassName.LAYOUT_FIXED)) {
        if (offset !== false) {
          $(Selector.CONTENT).css('min-height', ((max as number) + (offset as number)) - heights.header - heights.footer)
        }

        if (typeof ($.fn as Record<string, unknown>).overlayScrollbars !== 'undefined') {
          ($(Selector.SIDEBAR) as JQuery & { overlayScrollbars(opts: Record<string, unknown>): void }).overlayScrollbars({
            className       : this._config.scrollbarTheme,
            sizeAutoCapable : true,
            scrollbars : {
              autoHide: this._config.scrollbarAutoHide,
              clickScrolling : true
            }
          })
        }
      }
    }

    fixLoginRegisterHeight(): void {
      if ($(Selector.LOGIN_BOX + ', ' + Selector.REGISTER_BOX).length === 0) {
        $('body, html').css('height', 'auto')
      } else if ($(Selector.LOGIN_BOX + ', ' + Selector.REGISTER_BOX).length !== 0) {
        let box_height = $(Selector.LOGIN_BOX + ', ' + Selector.REGISTER_BOX).height()

        if ($('body').css('min-height') !== box_height) {
          $('body').css('min-height', box_height as number)
        }
      }
    }

    // Private

    private _init(): void {
      // Activate layout height watcher
      this.fixLayoutHeight()

      if (this._config.loginRegisterAutoHeight === true) {
        this.fixLoginRegisterHeight()
      } else if (Number.isInteger(this._config.loginRegisterAutoHeight)) {
        setInterval(this.fixLoginRegisterHeight, this._config.loginRegisterAutoHeight as number);
      }

      $(Selector.SIDEBAR)
        .on('collapsed.lte.treeview expanded.lte.treeview', () => {
          this.fixLayoutHeight()
        })

      $(Selector.PUSHMENU_BTN)
        .on('collapsed.lte.pushmenu shown.lte.pushmenu', () => {
          this.fixLayoutHeight()
        })

      $(Selector.CONTROL_SIDEBAR_BTN)
        .on('collapsed.lte.controlsidebar', () => {
          this.fixLayoutHeight()
        })
        .on('expanded.lte.controlsidebar', () => {
          this.fixLayoutHeight('control_sidebar')
        })

      $(window).resize(() => {
        this.fixLayoutHeight()
      })

      $('body.hold-transition').removeClass('hold-transition')
    }

    private _max(numbers: Record<string, number>): number {
      // Calculate the maximum number in a list
      let max = 0

      Object.keys(numbers).forEach((key) => {
        if (numbers[key] > max) {
          max = numbers[key]
        }
      })

      return max
    }

    // Static

    static _jQueryInterface(this: JQuery, config: string = ''): JQuery {
      return this.each(function () {
        let data = $(this).data(DATA_KEY) as Layout | undefined
        const _options = $.extend({}, Default, $(this).data()) as LayoutConfig

        if (!data) {
          data = new Layout($(this), _options)
          $(this).data(DATA_KEY, data)
        }

        if (config === 'init' || config === '') {
          data['_init']()
        } else if (config === 'fixLayoutHeight' || config === 'fixLoginRegisterHeight') {
          data[config]()
        }
      })
    }
  }

  /**
   * Data API
   * ====================================================
   */

  $(window).on('load', () => {
    Layout._jQueryInterface.call($('body'))
  })

  $(Selector.SIDEBAR + ' a').on('focusin', () => {
    $(Selector.MAIN_SIDEBAR).addClass(ClassName.SIDEBAR_FOCUSED);
  })

  $(Selector.SIDEBAR + ' a').on('focusout', () => {
    $(Selector.MAIN_SIDEBAR).removeClass(ClassName.SIDEBAR_FOCUSED);
  })

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = Layout._jQueryInterface
  $.fn[NAME].Constructor = Layout
  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return Layout._jQueryInterface
  }

  return Layout
})(jQuery)

export default Layout
