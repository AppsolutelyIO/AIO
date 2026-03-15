<?php

namespace Appsolutely\AIO\Tests\Unit;

use Appsolutely\AIO\AdminServiceProvider;
use ReflectionClass;

class AdminServiceProviderTest extends TestCase
{
    // --- compatibleBlade() should call Blade::withoutDoubleEncoding() directly ---

    public function test_compatible_blade_does_not_use_reflection()
    {
        $rc     = new ReflectionClass(AdminServiceProvider::class);
        $method = $rc->getMethod('compatibleBlade');

        $source    = file_get_contents($rc->getFileName());
        $startLine = $method->getStartLine();
        $endLine   = $method->getEndLine();

        $lines = array_slice(
            explode("\n", $source),
            $startLine - 1,
            $endLine - $startLine + 1
        );
        $methodSource = implode("\n", $lines);

        $this->assertStringNotContainsString(
            'ReflectionClass',
            $methodSource,
            'compatibleBlade() should not use ReflectionClass — withoutDoubleEncoding() is guaranteed in Laravel 11+'
        );
    }

    // --- Composer dependencies should only support Laravel 12+ ---

    public function test_composer_json_requires_laravel_12_or_higher()
    {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        $composer     = json_decode(file_get_contents($composerPath), true);

        $laravelConstraint = $composer['require']['laravel/framework'];

        $this->assertStringNotContainsString(
            '^10.0',
            $laravelConstraint,
            'Should not support Laravel 10'
        );
        $this->assertStringNotContainsString(
            '^11.0',
            $laravelConstraint,
            'Should not support Laravel 11'
        );
        $this->assertStringContainsString(
            '^12.0',
            $laravelConstraint,
            'Should support Laravel 12'
        );
    }

    public function test_composer_json_does_not_require_doctrine_dbal()
    {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        $composer     = json_decode(file_get_contents($composerPath), true);

        $this->assertArrayNotHasKey(
            'doctrine/dbal',
            $composer['require'],
            'doctrine/dbal was removed from Laravel 11 and is not used directly'
        );
    }

    public function test_composer_json_requires_spatie_sortable_v5_only()
    {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        $composer     = json_decode(file_get_contents($composerPath), true);

        $constraint = $composer['require']['spatie/eloquent-sortable'];

        $this->assertStringNotContainsString(
            '^4.0',
            $constraint,
            'spatie/eloquent-sortable v4 is for Laravel 10, should only require ^5.0'
        );
        $this->assertStringContainsString(
            '^5.0',
            $constraint,
            'Should require spatie/eloquent-sortable ^5.0 for Laravel 11+ support'
        );
    }

    public function test_composer_json_testbench_requires_v10_or_higher()
    {
        $composerPath = dirname(__DIR__, 2) . '/composer.json';
        $composer     = json_decode(file_get_contents($composerPath), true);

        $constraint = $composer['require-dev']['orchestra/testbench'];

        $this->assertStringNotContainsString(
            '^8.0',
            $constraint,
            'orchestra/testbench v8 is for Laravel 10 only'
        );
        $this->assertStringNotContainsString(
            '^9.0',
            $constraint,
            'orchestra/testbench v9 is for Laravel 11 only'
        );
        $this->assertStringContainsString(
            '^10.0',
            $constraint,
            'Should support testbench ^10.0 for Laravel 12'
        );
    }
}
