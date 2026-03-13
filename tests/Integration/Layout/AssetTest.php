<?php

namespace Appsolutely\AIO\Tests\Integration\Layout;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Layout\Asset;
use Appsolutely\AIO\Tests\Integration\TestCase;

class AssetTest extends TestCase
{
    protected function getAsset(): Asset
    {
        return app('admin.asset');
    }

    // --- Alias management ---

    public function test_has_default_admin_alias()
    {
        $asset = $this->getAsset();
        $this->assertTrue($asset->hasAlias('@admin'));
    }

    public function test_admin_alias_points_to_vendor_aio()
    {
        $asset = $this->getAsset();
        $this->assertSame('vendor/aio', $asset->alias('@admin'));
    }

    public function test_has_aio_alias()
    {
        $asset = $this->getAsset();
        $this->assertTrue($asset->hasAlias('@aio'));
    }

    public function test_extension_alias_points_correctly()
    {
        $asset = $this->getAsset();
        $this->assertSame('vendor/aio-extensions', $asset->alias('@extension'));
    }

    public function test_is_path_alias()
    {
        $asset = $this->getAsset();
        $this->assertTrue($asset->isPathAlias('@admin'));
        $this->assertFalse($asset->isPathAlias('@aio')); // @aio is array, not path alias
    }

    public function test_get_real_path_resolves_admin_alias()
    {
        $asset = $this->getAsset();
        $path = $asset->getRealPath('@admin/aio/js/app.js');
        $this->assertSame('vendor/aio/aio/js/app.js', $path);
    }

    public function test_get_real_path_returns_non_alias_unchanged()
    {
        $asset = $this->getAsset();
        $this->assertSame('path/to/file.js', $asset->getRealPath('path/to/file.js'));
    }

    // --- Custom alias ---

    public function test_set_custom_alias()
    {
        $asset = $this->getAsset();
        $asset->alias('@custom-lib', [
            'js'  => '@admin/custom/lib.js',
            'css' => '@admin/custom/lib.css',
        ]);

        $this->assertTrue($asset->hasAlias('@custom-lib'));
        $result = $asset->getAlias('@custom-lib');
        $this->assertArrayHasKey('js', $result);
        $this->assertArrayHasKey('css', $result);
    }

    public function test_alias_auto_prefixes_at_symbol()
    {
        $asset = $this->getAsset();
        $asset->alias('mylib', 'vendor/mylib');
        $this->assertTrue($asset->hasAlias('@mylib'));
    }

    public function test_set_multiple_aliases_via_array()
    {
        $asset = $this->getAsset();
        $asset->alias([
            '@lib1' => 'vendor/lib1',
            '@lib2' => 'vendor/lib2',
        ]);
        $this->assertTrue($asset->hasAlias('@lib1'));
        $this->assertTrue($asset->hasAlias('@lib2'));
    }

    // --- JS/CSS registration ---

    public function test_register_js()
    {
        $asset = $this->getAsset();
        $asset->js('custom.js');
        $this->assertContains('custom.js', $asset->js);
    }

    public function test_register_css()
    {
        $asset = $this->getAsset();
        $asset->css('custom.css');
        $this->assertContains('custom.css', $asset->css);
    }

    public function test_register_js_array()
    {
        $asset = $this->getAsset();
        $asset->js(['a.js', 'b.js']);
        $this->assertContains('a.js', $asset->js);
        $this->assertContains('b.js', $asset->js);
    }

    public function test_js_null_does_nothing()
    {
        $asset = $this->getAsset();
        $before = $asset->js;
        $asset->js(null);
        $this->assertSame($before, $asset->js);
    }

    public function test_css_null_does_nothing()
    {
        $asset = $this->getAsset();
        $before = $asset->css;
        $asset->css(null);
        $this->assertSame($before, $asset->css);
    }

    // --- Script/Style ---

    public function test_register_script()
    {
        $asset = $this->getAsset();
        $asset->script('console.log("test")');
        $this->assertContains('console.log("test")', $asset->script);
    }

    public function test_register_direct_script()
    {
        $asset = $this->getAsset();
        $asset->script('direct()', true);
        $this->assertContains('direct()', $asset->directScript);
    }

    public function test_register_style()
    {
        $asset = $this->getAsset();
        $asset->style('.foo { color: red; }');
        $this->assertContains('.foo { color: red; }', $asset->style);
    }

    // --- Require ---

    public function test_require_loads_alias_js_and_css()
    {
        $asset = $this->getAsset();
        $asset->alias('@test-lib', [
            'js'  => 'test-lib.js',
            'css' => 'test-lib.css',
        ]);
        $asset->require('@test-lib');

        $this->assertContains('test-lib.js', $asset->js);
        $this->assertContains('test-lib.css', $asset->css);
    }

    public function test_require_multiple_aliases()
    {
        $asset = $this->getAsset();
        $asset->alias('@lib-a', ['js' => 'a.js', 'css' => 'a.css']);
        $asset->alias('@lib-b', ['js' => 'b.js', 'css' => 'b.css']);
        $asset->require(['@lib-a', '@lib-b']);

        $this->assertContains('a.js', $asset->js);
        $this->assertContains('b.js', $asset->js);
    }

    // --- HTML rendering ---

    public function test_script_to_html_contains_aio_ready()
    {
        $asset = $this->getAsset();
        $asset->script('initApp()');
        $html = $asset->scriptToHtml();

        $this->assertStringContainsString('AIO.ready', $html);
        $this->assertStringContainsString('initApp()', $html);
        $this->assertStringNotContainsString('Dcat.ready', $html);
    }

    public function test_script_to_html_contains_direct_script()
    {
        $asset = $this->getAsset();
        $asset->script('directCall()', true);
        $html = $asset->scriptToHtml();

        $this->assertStringContainsString('directCall()', $html);
    }

    public function test_style_to_html()
    {
        $asset = $this->getAsset();
        $asset->style('.bar { display: none; }');
        $html = $asset->styleToHtml();

        $this->assertStringContainsString('<style>', $html);
        $this->assertStringContainsString('.bar { display: none; }', $html);
    }

    public function test_with_version_query()
    {
        $asset = $this->getAsset();
        $result = $asset->withVersionQuery('http://example.com/app.js');
        $this->assertStringContainsString('v'.Admin::VERSION, $result);
    }

    public function test_with_version_query_appends_to_existing()
    {
        $asset = $this->getAsset();
        $result = $asset->withVersionQuery('http://example.com/app.js?t=1');
        $this->assertStringContainsString('&v'.Admin::VERSION, $result);
    }

    // --- Base JS/CSS ---

    public function test_base_js_has_default_entries()
    {
        $asset = $this->getAsset();
        $this->assertArrayHasKey('adminlte', $asset->baseJs);
        $this->assertArrayHasKey('toastr', $asset->baseJs);
        $this->assertArrayHasKey('pjax', $asset->baseJs);
    }

    public function test_base_css_has_default_entries()
    {
        $asset = $this->getAsset();
        $this->assertArrayHasKey('adminlte', $asset->baseCss);
        $this->assertArrayHasKey('aio', $asset->baseCss);
    }

    public function test_header_js_has_aio()
    {
        $asset = $this->getAsset();
        $this->assertArrayHasKey('aio', $asset->headerJs);
    }

    public function test_set_base_js_replace()
    {
        $asset = $this->getAsset();
        $asset->baseJs(['custom' => 'custom.js'], false);
        $this->assertSame(['custom' => 'custom.js'], $asset->baseJs);
    }

    public function test_set_base_js_merge()
    {
        $asset = $this->getAsset();
        $original = $asset->baseJs;
        $asset->baseJs(['extra' => 'extra.js']);
        $this->assertArrayHasKey('extra', $asset->baseJs);
        $this->assertArrayHasKey('adminlte', $asset->baseJs);
    }

    public function test_set_base_css_replace()
    {
        $asset = $this->getAsset();
        $asset->baseCss(['custom' => 'custom.css']);
        $this->assertSame(['custom' => 'custom.css'], $asset->baseCss);
    }

    public function test_set_base_css_merge()
    {
        $asset = $this->getAsset();
        $asset->baseCss(['extra' => 'extra.css'], true);
        $this->assertArrayHasKey('extra', $asset->baseCss);
        $this->assertArrayHasKey('adminlte', $asset->baseCss);
    }

    // --- normalizeAliasPaths ---

    public function test_normalize_alias_paths_replaces_params()
    {
        $asset = $this->getAsset();
        $ref = new \ReflectionMethod($asset, 'normalizeAliasPaths');

        $result = $ref->invoke($asset, ['path/{version}/app.js'], ['version' => '1.0']);
        $this->assertSame(['path/1.0/app.js'], $result);
    }

    public function test_normalize_alias_paths_filters_unresolved_placeholders()
    {
        $asset = $this->getAsset();
        $ref = new \ReflectionMethod($asset, 'normalizeAliasPaths');

        $result = $ref->invoke($asset, [
            'resolved.js',
            '{unresolved}/file.js',
            'path/{missing}.js',
        ], []);

        // Only 'resolved.js' should remain — others contain unresolved '{'
        $this->assertSame(['resolved.js'], array_values($result));
    }

    public function test_normalize_alias_paths_filters_placeholder_at_start()
    {
        $asset = $this->getAsset();
        $ref = new \ReflectionMethod($asset, 'normalizeAliasPaths');

        // Bug test: '{foo}/bar.js' has '{' at position 0
        // mb_strpos returns 0, which is falsy — the file would NOT be filtered
        $result = $ref->invoke($asset, ['{foo}/bar.js'], []);
        $this->assertSame([], array_values($result));
    }

    // --- No dcat references ---

    public function test_no_dcat_in_default_aliases()
    {
        $asset = $this->getAsset();
        $aliases = $asset->getAlias('@aio');

        $jsFiles = (array) ($aliases['js'] ?? []);
        $cssFiles = (array) ($aliases['css'] ?? []);

        foreach (array_merge($jsFiles, $cssFiles) as $file) {
            $this->assertStringNotContainsString('dcat', $file,
                "Found 'dcat' in @aio alias files: {$file}");
        }
    }
}
