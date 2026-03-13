/**
 * --------------------------------------------
 * AdminLTE TodoList.ts
 * License MIT
 * --------------------------------------------
 */

interface TodoListConfig {
  onCheck: (item: JQuery) => JQuery
  onUnCheck: (item: JQuery) => JQuery
}

const TodoList = (($: JQueryStatic) => {
  /**
   * Constants
   * ====================================================
   */

  const NAME               = 'TodoList'
  const DATA_KEY           = 'lte.todolist'
  const EVENT_KEY          = `.${DATA_KEY}`
  const JQUERY_NO_CONFLICT = $.fn[NAME]

  const Selector = {
    DATA_TOGGLE: '[data-widget="todo-list"]'
  }

  const ClassName = {
    TODO_LIST_DONE: 'done'
  }

  const Default: TodoListConfig = {
    onCheck: function (item: JQuery): JQuery {
      return item;
    },
    onUnCheck: function (item: JQuery): JQuery {
      return item;
    }
  }

  /**
   * Class Definition
   * ====================================================
   */

  class TodoList {
    private _config: TodoListConfig
    private _element: JQuery

    constructor(element: JQuery, config: TodoListConfig) {
      this._config  = config
      this._element = element

      this._init()
    }

    // Public

    toggle(item: JQuery): void {
      item.parents('li').toggleClass(ClassName.TODO_LIST_DONE);
      if (! $(item).prop('checked')) {
        this.unCheck($(item));
        return;
      }

      this.check(item);
    }

    check(item: JQuery): void {
      this._config.onCheck.call(item);
    }

    unCheck(item: JQuery): void {
      this._config.onUnCheck.call(item);
    }

    // Private

    private _init(): void {
      const that = this
      $(Selector.DATA_TOGGLE).find('input:checkbox:checked').parents('li').toggleClass(ClassName.TODO_LIST_DONE)
      $(Selector.DATA_TOGGLE).on('change', 'input:checkbox', (event) => {
        that.toggle($(event.target))
      })
    }

    // Static

    static _jQueryInterface(this: JQuery, config: string): JQuery {
      return this.each(function () {
        let data = $(this).data(DATA_KEY) as TodoList | undefined
        const _options = $.extend({}, Default, $(this).data()) as TodoListConfig

        if (!data) {
          data = new TodoList($(this), _options)
          $(this).data(DATA_KEY, data)
        }

        if (config === 'init') {
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
    TodoList._jQueryInterface.call($(Selector.DATA_TOGGLE))
  })

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = TodoList._jQueryInterface
  $.fn[NAME].Constructor = TodoList
  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT
    return TodoList._jQueryInterface
  }

  return TodoList
})(jQuery)

export default TodoList
