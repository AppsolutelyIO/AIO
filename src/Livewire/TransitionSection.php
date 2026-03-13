<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Illuminate\Contracts\Container\Container;

final class TransitionSection extends GeneralBlock
{
    protected function initializeComponent(Container $container): void
    {
        $transitionSection = $this->displayOptions;
        $default           = $this->defaultConfig();

        $this->displayOptions = [
            'image'               => $transitionSection['image'] ?? $default['image'],
            'height'              => $transitionSection['height'] ?? $default['height'],
            'background_position' => $transitionSection['background_position'] ?? $default['background_position'],
            'background_size'     => $transitionSection['background_size'] ?? $default['background_size'],
            'overlay'             => array_merge($default['overlay'], $transitionSection['overlay'] ?? []),
            'responsive'          => array_merge($default['responsive'], $transitionSection['responsive'] ?? []),
        ];
    }

    /**
     * Use it in blade file
     */
    public function getBackgroundStyle(): string
    {
        $styles = [];
        $config = $this->displayOptions;

        // Height
        $styles[] = 'height: ' . $config['height'];

        // Mobile height as CSS custom property
        $styles[] = '--mobile-height: ' . $config['responsive']['mobile_height'];

        // Background image
        if (! empty($config['image'])) {
            $styles[] = 'background-image: url(' . asset_url($config['image']) . ')';
            $styles[] = 'background-position: ' . $config['background_position'];
            $styles[] = 'background-size: ' . $config['background_size'];
            $styles[] = 'background-repeat: no-repeat';
        }

        return implode('; ', $styles);
    }
}
