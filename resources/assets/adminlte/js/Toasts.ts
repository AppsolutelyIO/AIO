/**
 * --------------------------------------------
 * AdminLTE Toasts.ts
 * License MIT
 * --------------------------------------------
 */

interface ToastsConfig {
  position: string
  fixed: boolean
  autohide: boolean
  autoremove: boolean
  delay: number
  fade: boolean
  icon: string | null
  image: string | null
  imageAlt: string | null
  imageHeight: string
  title: string | null
  subtitle: string | null
  close: boolean
  body: string | null
  class: string | null
}

const Toasts = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'Toasts'
  const DATA_KEY           = 'lte.toasts'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]

  const Event = {
    INIT: `init${EVENT_KEY}`,
    CREATED: `created${EVENT_KEY}`,
    REMOVED: `removed${EVENT_KEY}`,
  }

  const Selector = {
    BODY: 'toast-body',
    CONTAINER_TOP_RIGHT: '#toastsContainerTopRight',
    CONTAINER_TOP_LEFT: '#toastsContainerTopLeft',
    CONTAINER_BOTTOM_RIGHT: '#toastsContainerBottomRight',
    CONTAINER_BOTTOM_LEFT: '#toastsContainerBottomLeft',
  }

  const ClassName = {
    TOP_RIGHT: 'toasts-top-right',
    TOP_LEFT: 'toasts-top-left',
    BOTTOM_RIGHT: 'toasts-bottom-right',
    BOTTOM_LEFT: 'toasts-bottom-left',
    FADE: 'fade',
  }

  const Position = {
    TOP_RIGHT: 'topRight',
    TOP_LEFT: 'topLeft',
    BOTTOM_RIGHT: 'bottomRight',
    BOTTOM_LEFT: 'bottomLeft',
  }

  const Id = {
    CONTAINER_TOP_RIGHT: 'toastsContainerTopRight',
    CONTAINER_TOP_LEFT: 'toastsContainerTopLeft',
    CONTAINER_BOTTOM_RIGHT: 'toastsContainerBottomRight',
    CONTAINER_BOTTOM_LEFT: 'toastsContainerBottomLeft',
  }

  const Default: ToastsConfig = {
    position: Position.TOP_RIGHT,
    fixed: true,
    autohide: false,
    autoremove: true,
    delay: 1000,
    fade: true,
    icon: null,
    image: null,
    imageAlt: null,
    imageHeight: '25px',
    title: null,
    subtitle: null,
    close: true,
    body: null,
    class: null,
  }

  /**
   * Class Definition
   * ====================================================
   */
  class Toasts {
    private _config: ToastsConfig

    constructor(_element: JQuery, config: ToastsConfig) {
      this._config  = config

      this._prepareContainer();

      const initEvent = $.Event(Event.INIT)
      $('body').trigger(initEvent)
    }

    // Public

    create(): void {
      var toast = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true"/>')

      toast.data('autohide', this._config.autohide)
      toast.data('animation', this._config.fade)

      if (this._config.class) {
        toast.addClass(this._config.class)
      }

      if (this._config.delay && this._config.delay != 500) {
        toast.data('delay', this._config.delay)
      }

      var toast_header = $('<div class="toast-header">')

      if (this._config.image != null) {
        var toast_image = $('<img />').addClass('rounded mr-2').attr('src', this._config.image).attr('alt', this._config.imageAlt as string)

        if (this._config.imageHeight != null) {
          toast_image.height(this._config.imageHeight).width('auto')
        }

        toast_header.append(toast_image)
      }

      if (this._config.icon != null) {
        toast_header.append($('<i />').addClass('mr-2').addClass(this._config.icon))
      }

      if (this._config.title != null) {
        toast_header.append($('<strong />').addClass('mr-auto').html(this._config.title))
      }

      if (this._config.subtitle != null) {
        toast_header.append($('<small />').html(this._config.subtitle))
      }

      if (this._config.close == true) {
        var toast_close = $('<button data-dismiss="toast" />').attr('type', 'button').addClass('ml-2 mb-1 close').attr('aria-label', 'Close').append('<span aria-hidden="true">&times;</span>')

        if (this._config.title == null) {
          toast_close.toggleClass('ml-2 ml-auto')
        }

        toast_header.append(toast_close)
      }

      toast.append(toast_header)

      if (this._config.body != null) {
        toast.append($('<div class="toast-body" />').html(this._config.body))
      }

      $(this._getContainerId() as string).prepend(toast)

      const createdEvent = $.Event(Event.CREATED)
      $('body').trigger(createdEvent)

      ;(toast as JQuery & { toast(action: string): void }).toast('show')


      if (this._config.autoremove) {
        toast.on('hidden.bs.toast', function () {
          $(this).delay(200).remove();

          const removedEvent = $.Event(Event.REMOVED)
          $('body').trigger(removedEvent)
        })
      }


    }

    // Private

    private _getContainerId(): string | undefined {
      if (this._config.position == Position.TOP_RIGHT) {
        return Selector.CONTAINER_TOP_RIGHT;
      } else if (this._config.position == Position.TOP_LEFT) {
        return Selector.CONTAINER_TOP_LEFT;
      } else if (this._config.position == Position.BOTTOM_RIGHT) {
        return Selector.CONTAINER_BOTTOM_RIGHT;
      } else if (this._config.position == Position.BOTTOM_LEFT) {
        return Selector.CONTAINER_BOTTOM_LEFT;
      }
    }

    private _prepareContainer(): void {
      if ($(this._getContainerId() as string).length === 0) {
        var container = $('<div />').attr('id', (this._getContainerId() as string).replace('#', ''))
        if (this._config.position == Position.TOP_RIGHT) {
          container.addClass(ClassName.TOP_RIGHT)
        } else if (this._config.position == Position.TOP_LEFT) {
          container.addClass(ClassName.TOP_LEFT)
        } else if (this._config.position == Position.BOTTOM_RIGHT) {
          container.addClass(ClassName.BOTTOM_RIGHT)
        } else if (this._config.position == Position.BOTTOM_LEFT) {
          container.addClass(ClassName.BOTTOM_LEFT)
        }

        $('body').append(container)
      }

      if (this._config.fixed) {
        $(this._getContainerId() as string).addClass('fixed')
      } else {
        $(this._getContainerId() as string).removeClass('fixed')
      }
    }

    // Static

    static _jQueryInterface(this: JQuery, option: string, config: Record<string, unknown>): JQuery {
      return this.each(function () {
        const _options = $.extend({}, Default, config) as ToastsConfig
        var toast = new Toasts($(this), _options)

        if (option === 'create') {
          toast[option]()
        }
      })
    }
  }

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = Toasts._jQueryInterface
  $.fn[NAME].Constructor = Toasts
  $.fn[NAME].noConflict  = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return Toasts._jQueryInterface
  }

  return Toasts
})(jQuery)

export default Toasts
