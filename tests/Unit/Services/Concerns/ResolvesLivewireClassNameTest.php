<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services\Concerns;

use Appsolutely\AIO\Services\Concerns\ResolvesLivewireClassName;
use Appsolutely\AIO\Tests\TestCase;

/**
 * Concrete class to test the trait methods (they are private).
 */
final class ResolvesLivewireClassNameTestable
{
    use ResolvesLivewireClassName;

    public function callResolveClassName(string $className): string
    {
        return $this->resolveClassName($className);
    }

    /** @return string[] */
    public function callClassNameVariants(string $className): array
    {
        return $this->classNameVariants($className);
    }
}

final class ResolvesLivewireClassNameTest extends TestCase
{
    private ResolvesLivewireClassNameTestable $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ResolvesLivewireClassNameTestable();
    }

    // --- resolveClassName ---

    public function test_resolve_class_name_maps_legacy_app_namespace_to_aio(): void
    {
        $result = $this->resolver->callResolveClassName('App\\Livewire\\GeneralBlock');

        $this->assertEquals('Appsolutely\\AIO\\Livewire\\GeneralBlock', $result);
    }

    public function test_resolve_class_name_returns_original_when_aio_class_not_found(): void
    {
        $result = $this->resolver->callResolveClassName('App\\Livewire\\NonExistentClass');

        $this->assertEquals('App\\Livewire\\NonExistentClass', $result);
    }

    public function test_resolve_class_name_returns_aio_namespace_unchanged(): void
    {
        $result = $this->resolver->callResolveClassName('Appsolutely\\AIO\\Livewire\\GeneralBlock');

        $this->assertEquals('Appsolutely\\AIO\\Livewire\\GeneralBlock', $result);
    }

    public function test_resolve_class_name_returns_unrelated_class_unchanged(): void
    {
        $result = $this->resolver->callResolveClassName('Some\\Other\\ClassName');

        $this->assertEquals('Some\\Other\\ClassName', $result);
    }

    // --- classNameVariants ---

    public function test_class_name_variants_returns_both_forms_for_app_namespace(): void
    {
        $result = $this->resolver->callClassNameVariants('App\\Livewire\\Header');

        $this->assertCount(2, $result);
        $this->assertContains('App\\Livewire\\Header', $result);
        $this->assertContains('Appsolutely\\AIO\\Livewire\\Header', $result);
    }

    public function test_class_name_variants_returns_both_forms_for_aio_namespace(): void
    {
        $result = $this->resolver->callClassNameVariants('Appsolutely\\AIO\\Livewire\\Footer');

        $this->assertCount(2, $result);
        $this->assertContains('Appsolutely\\AIO\\Livewire\\Footer', $result);
        $this->assertContains('App\\Livewire\\Footer', $result);
    }

    public function test_class_name_variants_returns_single_item_for_unrelated_class(): void
    {
        $result = $this->resolver->callClassNameVariants('Some\\Other\\ClassName');

        $this->assertCount(1, $result);
        $this->assertContains('Some\\Other\\ClassName', $result);
    }
}
