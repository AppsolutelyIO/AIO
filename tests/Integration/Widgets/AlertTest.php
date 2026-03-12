<?php

namespace Appsolutely\AIO\Tests\Integration\Widgets;

use Appsolutely\AIO\Tests\Integration\TestCase;
use Appsolutely\AIO\Widgets\Alert;

class AlertTest extends TestCase
{
    // --- Construction ---

    public function test_default_style_is_danger()
    {
        $alert = new Alert('Error message');
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-danger', $vars['attributes']);
        $this->assertSame('Error message', $vars['content']);
    }

    public function test_constructor_with_title()
    {
        $alert = new Alert('Message', 'Title');
        $vars = $alert->defaultVariables();

        $this->assertSame('Title', $vars['title']);
        $this->assertSame('Message', $vars['content']);
    }

    // --- Style methods ---

    public function test_info_style()
    {
        $alert = (new Alert('msg'))->info();
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-info', $vars['attributes']);
        $this->assertSame('fa fa-info', $vars['icon']);
    }

    public function test_success_style()
    {
        $alert = (new Alert('msg'))->success();
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-success', $vars['attributes']);
        $this->assertSame('fa fa-check', $vars['icon']);
    }

    public function test_warning_style()
    {
        $alert = (new Alert('msg'))->warning();
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-warning', $vars['attributes']);
        $this->assertSame('fa fa-warning', $vars['icon']);
    }

    public function test_danger_style()
    {
        $alert = (new Alert('msg'))->danger();
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-danger', $vars['attributes']);
        $this->assertSame('fa fa-ban', $vars['icon']);
    }

    public function test_primary_style()
    {
        $alert = (new Alert('msg'))->primary();
        $vars = $alert->defaultVariables();

        $this->assertStringContainsString('alert-primary', $vars['attributes']);
    }

    // --- Content and title ---

    public function test_content_method()
    {
        $alert = new Alert();
        $alert->content('Updated content');
        $vars = $alert->defaultVariables();

        $this->assertSame('Updated content', $vars['content']);
    }

    public function test_title_method()
    {
        $alert = new Alert();
        $alert->title('New Title');
        $vars = $alert->defaultVariables();

        $this->assertSame('New Title', $vars['title']);
    }

    // --- Icon ---

    public function test_custom_icon()
    {
        $alert = (new Alert('msg'))->icon('fa fa-star');
        $vars = $alert->defaultVariables();

        $this->assertSame('fa fa-star', $vars['icon']);
    }

    // --- Removable ---

    public function test_removable()
    {
        $alert = (new Alert('msg'))->removable();
        $vars = $alert->defaultVariables();

        $this->assertTrue($vars['showCloseBtn']);
    }

    public function test_not_removable_by_default()
    {
        $alert = new Alert('msg');
        $vars = $alert->defaultVariables();

        $this->assertFalse($vars['showCloseBtn']);
    }

    // --- Fluent chaining ---

    public function test_fluent_chaining()
    {
        $alert = (new Alert())
            ->content('Chain test')
            ->title('Chain title')
            ->success()
            ->icon('fa fa-thumbs-up')
            ->removable();

        $vars = $alert->defaultVariables();

        $this->assertSame('Chain test', $vars['content']);
        $this->assertSame('Chain title', $vars['title']);
        $this->assertStringContainsString('alert-success', $vars['attributes']);
        $this->assertSame('fa fa-thumbs-up', $vars['icon']);
        $this->assertTrue($vars['showCloseBtn']);
    }

    // --- Make static ---

    public function test_make_factory()
    {
        $alert = Alert::make('Factory content', 'Factory Title', 'info');
        $vars = $alert->defaultVariables();

        $this->assertSame('Factory content', $vars['content']);
        $this->assertSame('Factory Title', $vars['title']);
        $this->assertStringContainsString('alert-info', $vars['attributes']);
    }
}
