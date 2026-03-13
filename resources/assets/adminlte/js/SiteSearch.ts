/**
 * --------------------------------------------
 * AdminLTE SiteSearch.ts
 * License MIT
 * --------------------------------------------
 */

interface SiteSearchOptions {
  transitionSpeed: number
}

const SiteSearch = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'SiteSearch'
  const DATA_KEY           = 'lte.site-search'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]

  const Event: Record<string, string> = {}

  const Selector = {
    TOGGLE_BUTTON  : '[data-widget="site-search"]',
    SEARCH_BLOCK   : '.site-search-block',
    SEARCH_BACKDROP: '.site-search-backdrop',
    SEARCH_INPUT   : '.site-search-block .form-control'
  }

  const ClassName = {
    OPEN: 'site-search-open'
  }

  const Default: SiteSearchOptions = {
    transitionSpeed: 300
  }

  /**
   * Class Definition
   * ====================================================
   */

  class SiteSearch {
    private element: HTMLElement
    private options: SiteSearchOptions

    constructor(_element: HTMLElement, _options: SiteSearchOptions) {
      this.element = _element
      this.options = $.extend({}, Default, _options) as SiteSearchOptions
    }

    // Public

    open(): void {
      $(Selector.SEARCH_BLOCK).slideDown(this.options.transitionSpeed)
      $(Selector.SEARCH_BACKDROP).show(0)
      $(Selector.SEARCH_INPUT).focus()
      $(Selector.SEARCH_BLOCK).addClass(ClassName.OPEN)
    }

    close(): void {
      $(Selector.SEARCH_BLOCK).slideUp(this.options.transitionSpeed)
      $(Selector.SEARCH_BACKDROP).hide(0)
      $(Selector.SEARCH_BLOCK).removeClass(ClassName.OPEN)
    }

    toggle(): void {
      if ($(Selector.SEARCH_BLOCK).hasClass(ClassName.OPEN)) {
        this.close()
      } else {
        this.open()
      }
    }

    // Static

    static _jQueryInterface(this: JQuery, options: string): JQuery {
      return this.each(function () {
        let data = $(this).data(DATA_KEY) as SiteSearch | undefined

        if (!data) {
          data = new SiteSearch(this, options as unknown as SiteSearchOptions)
          $(this).data(DATA_KEY, data)
        }

        if (!/toggle|close/.test(options)) {
          throw Error(`Undefined method ${options}`)
        }

        (data as Record<string, unknown> & Record<string, () => void>)[options]()
      })
    }
  }

  /**
   * Data API
   * ====================================================
   */
  $(document).on('click', Selector.TOGGLE_BUTTON, (event) => {
    event.preventDefault()

    let button = $(event.currentTarget)

    if (button.data('widget') !== 'site-search') {
      button = button.closest(Selector.TOGGLE_BUTTON)
    }

    SiteSearch._jQueryInterface.call(button, 'toggle')
  })

  $(document).on('click', Selector.SEARCH_BACKDROP, (event) => {
    const backdrop = $(event.currentTarget)
    SiteSearch._jQueryInterface.call(backdrop, 'close')
  })

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = SiteSearch._jQueryInterface
  $.fn[NAME].Constructor = SiteSearch
  $.fn[NAME].noConflict  = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return SiteSearch._jQueryInterface
  }

  return SiteSearch
})(jQuery)

export default SiteSearch
