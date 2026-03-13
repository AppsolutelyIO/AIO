/**
 * --------------------------------------------
 * AdminLTE CardRefresh.ts
 * License MIT
 * --------------------------------------------
 */

interface CardRefreshSettings {
  source: string
  sourceSelector: string
  params: Record<string, unknown>
  trigger: string
  content: string
  loadInContent: boolean
  loadOnInit: boolean
  responseType: string
  overlayTemplate: string
  onLoadStart: () => void
  onLoadDone: (response: string) => string
}

const CardRefresh = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'CardRefresh'
  const DATA_KEY           = 'lte.cardrefresh'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]

  const Event = {
    LOADED: `loaded${EVENT_KEY}`,
    OVERLAY_ADDED: `overlay.added${EVENT_KEY}`,
    OVERLAY_REMOVED: `overlay.removed${EVENT_KEY}`,
  }

  const ClassName = {
    CARD: 'card',
  }

  const Selector = {
    CARD: `.${ClassName.CARD}`,
    DATA_REFRESH: '[data-card-widget="card-refresh"]',
  }

  const Default: CardRefreshSettings = {
    source: '',
    sourceSelector: '',
    params: {},
    trigger: Selector.DATA_REFRESH,
    content: '.card-body',
    loadInContent: true,
    loadOnInit: true,
    responseType: '',
    overlayTemplate: '<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>',
    onLoadStart: function () {
    },
    onLoadDone: function (response: string): string {
      return response;
    }
  }

  class CardRefresh {
    private _element: JQuery
    private _parent: JQuery
    private _settings: CardRefreshSettings
    private _overlay: JQuery

    constructor(element: JQuery, settings: CardRefreshSettings) {
      this._element  = element
      this._parent = element.parents(Selector.CARD).first()
      this._settings = $.extend({}, Default, settings) as CardRefreshSettings
      this._overlay = $(this._settings.overlayTemplate)

      if (element.hasClass(ClassName.CARD)) {
        this._parent = element
      }

      if (this._settings.source === '') {
        throw new Error('Source url was not defined. Please specify a url in your CardRefresh source option.');
      }
    }

    load(): void {
      this._addOverlay()
      this._settings.onLoadStart.call($(this))

      $.get(this._settings.source, this._settings.params, function (this: CardRefresh, response: string) {
        if (this._settings.loadInContent) {
          if (this._settings.sourceSelector != '') {
            response = $(response).find(this._settings.sourceSelector).html()
          }

          this._parent.find(this._settings.content).html(response)
        }

        this._settings.onLoadDone.call($(this), response)
        this._removeOverlay();
      }.bind(this), this._settings.responseType !== '' && this._settings.responseType)

      const loadedEvent = $.Event(Event.LOADED)
      $(this._element).trigger(loadedEvent)
    }

    private _addOverlay(): void {
      this._parent.append(this._overlay)

      const overlayAddedEvent = $.Event(Event.OVERLAY_ADDED)
      $(this._element).trigger(overlayAddedEvent)
    }

    private _removeOverlay(): void {
      this._parent.find(this._overlay).remove()

      const overlayRemovedEvent = $.Event(Event.OVERLAY_REMOVED)
      $(this._element).trigger(overlayRemovedEvent)
    }


    // Private

    _init(_card: JQuery): void {
      $(this as unknown as HTMLElement).find(this._settings.trigger).on('click', () => {
        this.load()
      })

      if (this._settings.loadOnInit) {
        this.load()
      }
    }

    // Static

    static _jQueryInterface(this: JQuery, config: string | Record<string, unknown>): void {
      let data = $(this).data(DATA_KEY) as CardRefresh | undefined
      const _options = $.extend({}, Default, $(this).data()) as CardRefreshSettings

      if (!data) {
        data = new CardRefresh($(this), _options)
        $(this).data(DATA_KEY, typeof config === 'string' ? data : config)
      }

      if (typeof config === 'string' && config.match(/load/)) {
        (data as Record<string, unknown> & Record<string, () => void>)[config]()
      } else {
        data._init($(this))
      }
    }
  }

  /**
   * Data API
   * ====================================================
   */

  $(document).on('click', Selector.DATA_REFRESH, function (event) {
    if (event) {
      event.preventDefault()
    }

    CardRefresh._jQueryInterface.call($(this), 'load')
  })

  $(document).ready(function () {
    $(Selector.DATA_REFRESH).each(function() {
      CardRefresh._jQueryInterface.call($(this))
    })
  })

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = CardRefresh._jQueryInterface
  $.fn[NAME].Constructor = CardRefresh
  $.fn[NAME].noConflict  = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return CardRefresh._jQueryInterface
  }

  return CardRefresh
})(jQuery)

export default CardRefresh
