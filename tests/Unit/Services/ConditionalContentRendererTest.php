<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Services\ConditionalContentRenderer;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\TestCase;

final class ConditionalContentRendererTest extends TestCase
{
    private function makeRenderer(string $uri = '/'): ConditionalContentRenderer
    {
        $request = Request::create($uri);

        return new ConditionalContentRenderer($request);
    }

    public function test_pure_html_passes_through_unchanged(): void
    {
        $html     = '<div class="hero"><h1>Hello World</h1></div>';
        $renderer = $this->makeRenderer();

        $this->assertSame($html, $renderer->render($html));
    }

    public function test_matching_if_returns_content(): void
    {
        $content  = '@if(request()->is(\'products/*\'))<h1>Products</h1>@endif';
        $renderer = $this->makeRenderer('/products/shoes');

        $this->assertSame('<h1>Products</h1>', $renderer->render($content));
    }

    public function test_non_matching_if_returns_empty(): void
    {
        $content  = '@if(request()->is(\'products/*\'))<h1>Products</h1>@endif';
        $renderer = $this->makeRenderer('/about');

        $this->assertSame('', $renderer->render($content));
    }

    public function test_if_else_returns_correct_branch(): void
    {
        $content = '@if(request()->is(\'products/*\'))<h1>Products</h1>@else<h1>Default</h1>@endif';

        $this->assertSame('<h1>Products</h1>', $this->makeRenderer('/products/shoes')->render($content));
        $this->assertSame('<h1>Default</h1>', $this->makeRenderer('/about')->render($content));
    }

    public function test_if_elseif_else_chain(): void
    {
        $content = '@if(request()->is(\'products/*\'))<h1>Products</h1>'
            . '@elseif(request()->is(\'about\'))<h1>About</h1>'
            . '@else<h1>Home</h1>@endif';

        $this->assertSame('<h1>Products</h1>', $this->makeRenderer('/products/shoes')->render($content));
        $this->assertSame('<h1>About</h1>', $this->makeRenderer('/about')->render($content));
        $this->assertSame('<h1>Home</h1>', $this->makeRenderer('/contact')->render($content));
    }

    public function test_mixed_html_and_conditional(): void
    {
        $content = '<header>Nav</header>@if(request()->is(\'about\'))<main>About</main>@endif<footer>Foot</footer>';

        $this->assertSame(
            '<header>Nav</header><main>About</main><footer>Foot</footer>',
            $this->makeRenderer('/about')->render($content),
        );
        $this->assertSame(
            '<header>Nav</header><footer>Foot</footer>',
            $this->makeRenderer('/home')->render($content),
        );
    }

    public function test_negation_with_exclamation(): void
    {
        $content = '@if(!request()->is(\'admin/*\'))<p>Public</p>@else<p>Admin</p>@endif';

        $this->assertSame('<p>Public</p>', $this->makeRenderer('/about')->render($content));
        $this->assertSame('<p>Admin</p>', $this->makeRenderer('/admin/dashboard')->render($content));
    }

    public function test_negation_with_space(): void
    {
        $content = '@if(! request()->is(\'admin/*\'))<p>Public</p>@endif';

        $this->assertSame('<p>Public</p>', $this->makeRenderer('/about')->render($content));
        $this->assertSame('', $this->makeRenderer('/admin/dashboard')->render($content));
    }

    public function test_double_quoted_pattern(): void
    {
        $content = '@if(request()->is("products/*"))<h1>Products</h1>@endif';

        $this->assertSame('<h1>Products</h1>', $this->makeRenderer('/products/shoes')->render($content));
    }

    public function test_multiple_patterns(): void
    {
        $content = '@if(request()->is(\'products/*\', \'services/*\'))<p>Match</p>@endif';

        $this->assertSame('<p>Match</p>', $this->makeRenderer('/products/shoes')->render($content));
        $this->assertSame('<p>Match</p>', $this->makeRenderer('/services/repair')->render($content));
        $this->assertSame('', $this->makeRenderer('/about')->render($content));
    }

    public function test_nested_if_blocks(): void
    {
        $content = '@if(request()->is(\'products/*\'))'
            . '<div>@if(request()->is(\'products/shoes\'))<span>Shoes</span>@else<span>Other</span>@endif</div>'
            . '@endif';

        $this->assertSame(
            '<div><span>Shoes</span></div>',
            $this->makeRenderer('/products/shoes')->render($content),
        );
        $this->assertSame(
            '<div><span>Other</span></div>',
            $this->makeRenderer('/products/bags')->render($content),
        );
        $this->assertSame('', $this->makeRenderer('/about')->render($content));
    }

    public function test_unknown_condition_evaluates_to_false(): void
    {
        $content = '@if(auth()->check())<p>Logged in</p>@else<p>Guest</p>@endif';

        $this->assertSame('<p>Guest</p>', $this->makeRenderer()->render($content));
    }

    public function test_route_is_condition(): void
    {
        $content = '@if(request()->routeIs(\'home\'))<p>Home</p>@endif';
        $request = Request::create('/');
        $request->setRouteResolver(function () {
            $route = new Route('GET', '/', fn () => '');
            $route->name('home');

            return $route;
        });

        $renderer = new ConditionalContentRenderer($request);
        $this->assertSame('<p>Home</p>', $renderer->render($content));
    }

    public function test_malicious_php_code_is_not_executed(): void
    {
        $content = '@if(system(\'whoami\'))<p>Hacked</p>@else<p>Safe</p>@endif';

        $this->assertSame('<p>Safe</p>', $this->makeRenderer()->render($content));
    }

    public function test_blade_render_directive_is_not_executed(): void
    {
        $content = '@if(true)<p>{{ config("app.key") }}</p>@endif';

        // The {{ }} should NOT be processed — it's raw output
        $this->assertSame('', $this->makeRenderer()->render($content));
    }

    public function test_empty_content(): void
    {
        $this->assertSame('', $this->makeRenderer()->render(''));
    }

    public function test_multiple_independent_blocks(): void
    {
        $content = '@if(request()->is(\'products/*\'))<p>A</p>@endif'
            . '<hr>'
            . '@if(request()->is(\'products/shoes\'))<p>B</p>@endif';

        $this->assertSame(
            '<p>A</p><hr><p>B</p>',
            $this->makeRenderer('/products/shoes')->render($content),
        );
        $this->assertSame(
            '<p>A</p><hr>',
            $this->makeRenderer('/products/bags')->render($content),
        );
    }
}
