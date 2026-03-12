<?php

namespace Appsolutely\AIO\Tests\Integration\Widgets;

use Appsolutely\AIO\Tests\Integration\TestCase;
use Appsolutely\AIO\Widgets\Card;

class CardTest extends TestCase
{
    // --- Construction ---

    public function test_constructor_with_content_only()
    {
        $card = new Card('Content here');
        $vars = $card->defaultVariables();

        $this->assertSame('', $vars['title']);
        $this->assertSame('Content here', $vars['content']);
    }

    public function test_constructor_with_title_and_content()
    {
        $card = new Card('My Title', 'My Content');
        $vars = $card->defaultVariables();

        $this->assertSame('My Title', $vars['title']);
        $this->assertSame('My Content', $vars['content']);
    }

    // --- Card class ---

    public function test_has_card_class()
    {
        $card = new Card('test');
        $vars = $card->defaultVariables();

        $this->assertStringContainsString('card', $vars['attributes']);
    }

    public function test_has_auto_id()
    {
        $card = new Card('test');
        $vars = $card->defaultVariables();

        $this->assertStringContainsString('id="card-', $vars['attributes']);
    }

    // --- Methods ---

    public function test_title()
    {
        $card = new Card('content');
        $card->title('New Title');
        $vars = $card->defaultVariables();

        $this->assertSame('New Title', $vars['title']);
    }

    public function test_content()
    {
        $card = new Card('old');
        $card->content('new content');
        $vars = $card->defaultVariables();

        $this->assertSame('new content', $vars['content']);
    }

    public function test_footer()
    {
        $card = new Card('content');
        $card->footer('Footer text');
        $vars = $card->defaultVariables();

        $this->assertSame('Footer text', $vars['footer']);
    }

    public function test_tool()
    {
        $card = new Card('content');
        $card->tool('<button>Refresh</button>');
        $vars = $card->defaultVariables();

        $this->assertCount(1, $vars['tools']);
        $this->assertSame('<button>Refresh</button>', $vars['tools'][0]);
    }

    public function test_multiple_tools()
    {
        $card = new Card('content');
        $card->tool('Tool A');
        $card->tool('Tool B');
        $vars = $card->defaultVariables();

        $this->assertCount(2, $vars['tools']);
    }

    // --- Divider ---

    public function test_divider_default_false()
    {
        $card = new Card('content');
        $vars = $card->defaultVariables();

        $this->assertFalse($vars['divider']);
    }

    public function test_with_header_border()
    {
        $card = (new Card('content'))->withHeaderBorder();
        $vars = $card->defaultVariables();

        $this->assertTrue($vars['divider']);
    }

    // --- Padding ---

    public function test_padding()
    {
        $card = (new Card('content'))->padding('10px');
        $vars = $card->defaultVariables();

        $this->assertSame('padding:10px', $vars['padding']);
    }

    public function test_no_padding()
    {
        $card = (new Card('content'))->noPadding();
        $vars = $card->defaultVariables();

        $this->assertSame('padding:0', $vars['padding']);
    }

    public function test_default_padding_is_null()
    {
        $card = new Card('content');
        $vars = $card->defaultVariables();

        $this->assertNull($vars['padding']);
    }

    // --- Make ---

    public function test_make_factory()
    {
        $card = Card::make('Title', 'Body');
        $vars = $card->defaultVariables();

        $this->assertSame('Title', $vars['title']);
        $this->assertSame('Body', $vars['content']);
    }

    // --- Fluent ---

    public function test_fluent_chaining()
    {
        $card = (new Card())
            ->title('Chained')
            ->content('Body')
            ->footer('Foot')
            ->withHeaderBorder()
            ->padding('20px');

        $vars = $card->defaultVariables();

        $this->assertSame('Chained', $vars['title']);
        $this->assertSame('Body', $vars['content']);
        $this->assertSame('Foot', $vars['footer']);
        $this->assertTrue($vars['divider']);
        $this->assertSame('padding:20px', $vars['padding']);
    }
}
