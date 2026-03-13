<?php

namespace Appsolutely\AIO\Tests\Unit\Form;

use Appsolutely\AIO\Form\Field\ImageField;
use Appsolutely\AIO\Tests\Unit\TestCase;

class ImageFieldTest extends TestCase
{
    private function createInstance()
    {
        return new class {
            use ImageField {
                ImageField::__call as imageCall;
            }

            protected $thumbnails = [];

            public function getThumbnails(): array
            {
                return $this->thumbnails;
            }

            public static function hasMacro($name): bool
            {
                return false;
            }
        };
    }

    // --- ImageField::thumbnail() with array ---

    public function test_thumbnail_with_array_sets_multiple_thumbnails()
    {
        $instance = $this->createInstance();
        $result = $instance->thumbnail([
            'small' => [100, 100],
            'medium' => [300, 300],
        ]);

        $this->assertSame($instance, $result);
        $this->assertSame([
            'small' => [100, 100],
            'medium' => [300, 300],
        ], $instance->getThumbnails());
    }

    public function test_thumbnail_with_array_skips_invalid_sizes()
    {
        $instance = $this->createInstance();
        $instance->thumbnail([
            'valid' => [100, 100],
            'invalid' => [100], // Only one dimension
        ]);

        $this->assertSame(['valid' => [100, 100]], $instance->getThumbnails());
    }

    // --- ImageField::thumbnail() with name, width, height ---

    public function test_thumbnail_with_three_args_sets_single_thumbnail()
    {
        $instance = $this->createInstance();
        $result = $instance->thumbnail('small', 100, 100);

        $this->assertSame($instance, $result);
        $this->assertSame(['small' => [100, 100]], $instance->getThumbnails());
    }

    // --- ImageField::thumbnail() with invalid args ---

    public function test_thumbnail_with_only_string_name_does_nothing()
    {
        $instance = $this->createInstance();
        $instance->thumbnail('small');

        $this->assertSame([], $instance->getThumbnails());
    }
}
