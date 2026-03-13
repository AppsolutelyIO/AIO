<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Repositories\ProductAttributeGroupRepository;
use Appsolutely\AIO\Repositories\ProductAttributeRepository;
use Appsolutely\AIO\Repositories\ProductAttributeValueRepository;
use Appsolutely\AIO\Services\Contracts\ProductAttributeServiceInterface;
use Illuminate\Database\Eloquent\Collection;

final readonly class ProductAttributeService implements ProductAttributeServiceInterface
{
    public function __construct(
        protected ProductAttributeGroupRepository $attributeGroupRepository,
        protected ProductAttributeRepository $attributeRepository,
        protected ProductAttributeValueRepository $attributeValueRepository,
    ) {}

    public static function attributeCacheKey(string $groupId, string $key): string
    {
        return sprintf('%s.attributes.%s_%s', appsolutely(), $groupId, $key);
    }

    public function findAttributesByGroupId(int|string $groupId): array
    {
        $data         = $this->attributeGroupRepository->with(['attributes.values'])->find($groupId);
        $attributes   =  $data?->attributes;
        $array        = self::collection2Array($attributes);
        $combinations = self::combinations($array);

        return self::formattedCombination($combinations, $groupId);
    }

    protected function collection2Array(?Collection $attributes): array
    {
        if ($attributes === null) {
            return [];
        }

        // Transform Eloquent collection to array and enrich values with attribute metadata
        return $attributes->map(function ($attribute) {
            $item           = $attribute->toArray();

            // Enrich each value with its parent attribute's title and slug
            // This makes it easier to identify which attribute a value belongs to
            $item['values'] = collect($item['values'])->map(function ($value) use ($attribute) {
                return array_merge($value, [
                    'attribute_title' => $attribute->title,
                    'attribute_slug'  => $attribute->slug,
                ]);
            })->all();

            return $item;
        })->all();
    }

    protected function combinations(array $array): array
    {
        if (empty($array)) {
            return [];
        }

        // Extract value arrays from each attribute
        // Example: [['values' => [1,2]], ['values' => [3,4]]] => [[1,2], [3,4]]
        $valueSets = array_map(fn ($attr) => $attr['values'], $array);

        if (in_array([], $valueSets, true)) {
            return [];
        }

        // Generate all possible combinations using cross join
        // Example: [[1,2], [3,4]] => [[1,3], [1,4], [2,3], [2,4]]
        return \Arr::crossJoin(...$valueSets);
    }

    protected function formattedCombination(array $combinations, string $groupId): array
    {
        // Transform each combination into a structured format with cache keys
        return array_reduce($combinations, function (array $item, $combo) use ($groupId) {
            // Build query strings for different lookup methods:
            // - keys: attribute_id => value_id mapping (for database lookups)
            // - titles: attribute_title => value mapping (for display)
            // - slugs: attribute_slug => value_slug mapping (for URLs)
            $keys       = http_build_query(collect($combo)->sortBy('attribute_id')->pluck('id', 'attribute_id')->toArray());
            $titles     = http_build_query(collect($combo)->sortBy('attribute_title')->pluck('value', 'attribute_title')->toArray());
            $slugs      = http_build_query(collect($combo)->sortBy('attribute_slug')->pluck('slug', 'attribute_slug')->toArray());

            // Generate unique cache key for this combination
            $key        = self::attributeCacheKey($groupId, $keys);

            // Build formatted combination data structure
            $item[$key] =  [
                'key'      => $key,      // Unique cache key
                'keys'     => $keys,     // Query string: attribute_id=value_id&...
                'title'    => $titles,   // Query string: attribute_title=value&...
                'slug'     => $slugs,    // Query string: attribute_slug=value_slug&...
                'readable' => str_replace(['&', '='], ['; ', ': '], $titles), // Human-readable format
                'data'     => $combo,    // Raw combination data
            ];

            // Cache the formatted combination for quick retrieval
            cache()->put($key, $item[$key]);

            return $item;
        }, []);
    }

    protected function flattenCombination(array $combinations, string $field): array
    {
        return array_map(fn ($item) => $item[$field], $combinations);
    }
}
