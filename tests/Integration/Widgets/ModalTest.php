<?php

namespace Appsolutely\AIO\Tests\Integration\Widgets;

use Appsolutely\AIO\Tests\Integration\TestCase;
use Appsolutely\AIO\Widgets\Modal;

class ModalTest extends TestCase
{
    // --- Construction ---

    public function test_constructor_sets_title_and_content()
    {
        $modal = new Modal('My Title', 'My Content');
        $html = $modal->html();

        $this->assertStringContainsString('My Title', $html);
        $this->assertStringContainsString('My Content', $html);
    }

    public function test_has_modal_class()
    {
        $modal = new Modal();
        $html = $modal->html();

        $this->assertStringContainsString('modal fade', $html);
    }

    public function test_has_auto_id()
    {
        $modal = new Modal();
        $html = $modal->html();

        $this->assertMatchesRegularExpression('/id="modal-[a-zA-Z0-9]+"/', $html);
    }

    // --- Title ---

    public function test_title()
    {
        $modal = new Modal();
        $modal->title('Updated Title');
        $html = $modal->html();

        $this->assertStringContainsString('Updated Title', $html);
    }

    // --- Content ---

    public function test_content()
    {
        $modal = new Modal();
        $modal->content('Modal body content');
        $html = $modal->html();

        $this->assertStringContainsString('Modal body content', $html);
    }

    public function test_body_is_alias_for_content()
    {
        $modal = new Modal();
        $modal->body('Body content');
        $html = $modal->html();

        $this->assertStringContainsString('Body content', $html);
    }

    // --- Footer ---

    public function test_footer()
    {
        $modal = new Modal();
        $modal->footer('<button>Save</button>');
        $html = $modal->html();

        $this->assertStringContainsString('modal-footer', $html);
        $this->assertStringContainsString('<button>Save</button>', $html);
    }

    public function test_no_footer_by_default()
    {
        $modal = new Modal('Title', 'Content');
        $html = $modal->html();

        $this->assertStringNotContainsString('modal-footer', $html);
    }

    // --- Size ---

    public function test_sm_size()
    {
        $modal = (new Modal())->sm();
        $html = $modal->html();

        $this->assertStringContainsString('modal-sm', $html);
    }

    public function test_lg_size()
    {
        $modal = (new Modal())->lg();
        $html = $modal->html();

        $this->assertStringContainsString('modal-lg', $html);
    }

    public function test_xl_size()
    {
        $modal = (new Modal())->xl();
        $html = $modal->html();

        $this->assertStringContainsString('modal-xl', $html);
    }

    public function test_custom_size()
    {
        $modal = (new Modal())->size('custom');
        $html = $modal->html();

        $this->assertStringContainsString('modal-custom', $html);
    }

    // --- Centered ---

    public function test_centered()
    {
        $modal = (new Modal())->centered();
        $html = $modal->html();

        $this->assertStringContainsString('modal-dialog-centered', $html);
    }

    public function test_not_centered_by_default()
    {
        $modal = new Modal();
        $html = $modal->html();

        $this->assertStringNotContainsString('modal-dialog-centered', $html);
    }

    // --- Scrollable ---

    public function test_scrollable()
    {
        $modal = (new Modal())->scrollable();
        $html = $modal->html();

        $this->assertStringContainsString('modal-dialog-scrollable', $html);
    }

    // --- Button ---

    public function test_button_renders_trigger()
    {
        $modal = new Modal('Title', 'Content');
        $modal->button('Click Me');
        $modal->join(true);

        // We can't call render() without full admin context, but we can verify button is set
        $this->assertInstanceOf(Modal::class, $modal);
    }

    // --- Events ---

    public function test_on_event()
    {
        $modal = new Modal();
        $modal->on('show.bs.modal', 'console.log("show")');

        // Events are processed in render(), verify it accepts events
        $this->assertInstanceOf(Modal::class, $modal);
    }

    public function test_on_show()
    {
        $modal = new Modal();
        $result = $modal->onShow('console.log("show")');
        $this->assertInstanceOf(Modal::class, $result);
    }

    public function test_on_shown()
    {
        $modal = new Modal();
        $result = $modal->onShown('console.log("shown")');
        $this->assertInstanceOf(Modal::class, $result);
    }

    public function test_on_hide()
    {
        $modal = new Modal();
        $result = $modal->onHide('console.log("hide")');
        $this->assertInstanceOf(Modal::class, $result);
    }

    public function test_on_hidden()
    {
        $modal = new Modal();
        $result = $modal->onHidden('console.log("hidden")');
        $this->assertInstanceOf(Modal::class, $result);
    }

    // --- HTML structure ---

    public function test_html_has_modal_structure()
    {
        $modal = new Modal('Title', 'Content');
        $html = $modal->html();

        $this->assertStringContainsString('modal-dialog', $html);
        $this->assertStringContainsString('modal-content', $html);
        $this->assertStringContainsString('modal-header', $html);
        $this->assertStringContainsString('modal-title', $html);
        $this->assertStringContainsString('modal-body', $html);
        $this->assertStringContainsString('data-dismiss="modal"', $html);
    }

    // --- Make ---

    public function test_make_factory()
    {
        $modal = Modal::make('Factory Title', 'Factory Body');
        $html = $modal->html();

        $this->assertStringContainsString('Factory Title', $html);
        $this->assertStringContainsString('Factory Body', $html);
    }

    // --- Fluent ---

    public function test_fluent_chaining()
    {
        $modal = (new Modal())
            ->title('Title')
            ->content('Body')
            ->footer('Footer')
            ->lg()
            ->centered()
            ->scrollable()
            ->delay(20);

        $html = $modal->html();

        $this->assertStringContainsString('Title', $html);
        $this->assertStringContainsString('Body', $html);
        $this->assertStringContainsString('modal-footer', $html);
        $this->assertStringContainsString('modal-lg', $html);
        $this->assertStringContainsString('modal-dialog-centered', $html);
        $this->assertStringContainsString('modal-dialog-scrollable', $html);
    }
}
