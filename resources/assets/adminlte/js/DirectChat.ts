/**
 * --------------------------------------------
 * AdminLTE DirectChat.ts
 * License MIT
 * --------------------------------------------
 */

const DirectChat = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'DirectChat'
  const DATA_KEY           = 'lte.directchat'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]
  const DATA_API_KEY       = '.data-api'

  const Event = {
    TOGGLED: `toggled{EVENT_KEY}`
  }

  const Selector = {
    DATA_TOGGLE: '[data-widget="chat-pane-toggle"]',
    DIRECT_CHAT: '.direct-chat'
  };

  const ClassName = {
    DIRECT_CHAT_OPEN: 'direct-chat-contacts-open'
  };

  /**
   * Class Definition
   * ====================================================
   */

  class DirectChat {
    private _element: JQuery

    constructor(element: JQuery, _config?: Record<string, unknown>) {
      this._element = element
    }

    toggle(): void {
      $(this._element).parents(Selector.DIRECT_CHAT).first().toggleClass(ClassName.DIRECT_CHAT_OPEN);

      const toggledEvent = $.Event(Event.TOGGLED)
      $(this._element).trigger(toggledEvent)
    }

    // Static

    static _jQueryInterface(this: JQuery, config: string): JQuery {
      return this.each(function () {
        let data = $(this).data(DATA_KEY) as DirectChat | undefined

        if (!data) {
          data = new DirectChat($(this))
          $(this).data(DATA_KEY, data)
        }

        (data as Record<string, unknown> & Record<string, () => void>)[config]()
      })
    }
  }

  /**
   *
   * Data Api implementation
   * ====================================================
   */

  $(document).on('click', Selector.DATA_TOGGLE, function (event) {
    if (event) event.preventDefault();
    DirectChat._jQueryInterface.call($(this), 'toggle');
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = DirectChat._jQueryInterface
  $.fn[NAME].Constructor = DirectChat
  $.fn[NAME].noConflict  = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return DirectChat._jQueryInterface
  }

  return DirectChat
})(jQuery)

export default DirectChat
