<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Appsolutely\AIO\Models\PageBlock;
use Illuminate\Database\Seeder;

final class PageBlockSchemaSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateBlockSchemas();
    }

    private function updateBlockSchemas(): void
    {
        // Header Schema
        $this->updateBlockSchema('header', [
            'brand_name' => [
                'type'        => 'text',
                'label'       => 'Brand Name',
                'description' => 'Company or site name',
                'required'    => true,
                'max_length'  => 50,
                'default'     => 'Your Logo',
            ],
            'brand_logo' => [
                'type'        => 'image',
                'label'       => 'Brand Logo',
                'description' => 'Company logo image',
                'required'    => false,
                'max_size'    => '2MB',
            ],
            'menu_items' => [
                'type'        => 'table',
                'label'       => 'Menu Items',
                'description' => 'Navigation menu items',
                'max_items'   => 10,
                'fields'      => [
                    'label' => [
                        'type'       => 'text',
                        'label'      => 'Menu Label',
                        'required'   => true,
                        'max_length' => 50,
                    ],
                    'url' => [
                        'type'     => 'text',
                        'label'    => 'URL',
                        'required' => true,
                    ],
                    'icon' => [
                        'type'       => 'text',
                        'label'      => 'Icon Class',
                        'required'   => false,
                        'max_length' => 50,
                    ],
                    'is_active' => [
                        'type'    => 'boolean',
                        'label'   => 'Active',
                        'default' => true,
                    ],
                ],
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Choose the header background color',
                'required'    => true,
                'options'     => [
                    ['value' => 'bg-light', 'label' => 'Light'],
                    ['value' => 'bg-dark', 'label' => 'Dark'],
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                    ['value' => 'bg-transparent', 'label' => 'Transparent'],
                ],
                'default' => 'bg-light',
            ],
        ]);

        // Footer Schema
        $this->updateBlockSchema('footer', [
            'company_name' => [
                'type'        => 'text',
                'label'       => 'Company Name',
                'description' => 'Company name for the footer',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Your Company',
            ],
            'copyright_text' => [
                'type'        => 'text',
                'label'       => 'Copyright Text',
                'description' => 'Copyright notice text',
                'required'    => false,
                'max_length'  => 200,
                'default'     => 'All rights reserved.',
            ],
            'footer_links' => [
                'type'        => 'table',
                'label'       => 'Footer Links',
                'description' => 'Links to display in the footer',
                'max_items'   => 10,
                'fields'      => [
                    'label' => [
                        'type'       => 'text',
                        'label'      => 'Link Label',
                        'required'   => true,
                        'max_length' => 50,
                    ],
                    'url' => [
                        'type'     => 'text',
                        'label'    => 'URL',
                        'required' => true,
                    ],
                ],
            ],
            'social_links' => [
                'type'        => 'table',
                'label'       => 'Social Media Links',
                'description' => 'Social media links',
                'max_items'   => 5,
                'fields'      => [
                    'platform' => [
                        'type'     => 'select',
                        'label'    => 'Platform',
                        'required' => true,
                        'options'  => [
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'twitter', 'label' => 'Twitter'],
                            ['value' => 'instagram', 'label' => 'Instagram'],
                            ['value' => 'linkedin', 'label' => 'LinkedIn'],
                            ['value' => 'youtube', 'label' => 'YouTube'],
                        ],
                    ],
                    'url' => [
                        'type'     => 'url',
                        'label'    => 'Profile URL',
                        'required' => true,
                    ],
                ],
            ],
        ]);

        // Hero Banner Schema
        $this->updateBlockSchema('hero-banner', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Hero Title',
                'description' => 'Main headline for the hero section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Welcome to Our Platform',
            ],
            'subtitle' => [
                'type'        => 'textarea',
                'label'       => 'Hero Subtitle',
                'description' => 'Supporting text below the main title',
                'required'    => false,
                'max_length'  => 200,
                'default'     => 'Transform your business with our innovative solutions. Get started today and see the difference.',
            ],
            'background_image' => [
                'type'        => 'image',
                'label'       => 'Background Image',
                'description' => 'Hero background image',
                'required'    => false,
                'max_size'    => '5MB',
            ],
            'hero_image' => [
                'type'        => 'image',
                'label'       => 'Hero Image',
                'description' => 'Main image for the hero section',
                'required'    => false,
                'max_size'    => '5MB',
            ],
            'cta_buttons' => [
                'type'        => 'table',
                'label'       => 'Call to Action Buttons',
                'description' => 'Buttons to display in the hero section',
                'max_items'   => 3,
                'fields'      => [
                    'text' => [
                        'type'       => 'text',
                        'label'      => 'Button Text',
                        'required'   => true,
                        'max_length' => 50,
                    ],
                    'url' => [
                        'type'     => 'text',
                        'label'    => 'Button URL',
                        'required' => true,
                    ],
                    'style' => [
                        'type'     => 'select',
                        'label'    => 'Button Style',
                        'required' => true,
                        'options'  => [
                            ['value' => 'btn-light', 'label' => 'Light'],
                            ['value' => 'btn-outline-light', 'label' => 'Outline Light'],
                            ['value' => 'btn-primary', 'label' => 'Primary'],
                            ['value' => 'btn-outline-primary', 'label' => 'Outline Primary'],
                        ],
                        'default' => 'btn-light',
                    ],
                ],
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Choose the hero background color',
                'required'    => true,
                'options'     => [
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                    ['value' => 'bg-secondary', 'label' => 'Secondary'],
                    ['value' => 'bg-dark', 'label' => 'Dark'],
                    ['value' => 'bg-light', 'label' => 'Light'],
                ],
                'default' => 'bg-primary',
            ],
        ]);

        // Media Slider Schema
        $this->updateBlockSchema('media-slider', [
            'slides' => [
                'type'        => 'table',
                'label'       => 'Slides',
                'description' => 'Slides for the carousel',
                'max_items'   => 10,
                'fields'      => [
                    'title' => [
                        'type'       => 'text',
                        'label'      => 'Slide Title',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                    'description' => [
                        'type'       => 'textarea',
                        'label'      => 'Slide Description',
                        'required'   => false,
                        'max_length' => 200,
                    ],
                    'media_type' => [
                        'type'     => 'select',
                        'label'    => 'Media Type',
                        'required' => true,
                        'options'  => [
                            ['value' => 'image', 'label' => 'Image'],
                            ['value' => 'video', 'label' => 'Video'],
                        ],
                        'default' => 'image',
                    ],
                    'media_url' => [
                        'type'        => 'text',
                        'label'       => 'Media URL',
                        'description' => 'URL for image or video file',
                        'required'    => true,
                    ],
                    'alt_text' => [
                        'type'        => 'text',
                        'label'       => 'Alt Text',
                        'description' => 'Alternative text for accessibility',
                        'required'    => false,
                        'max_length'  => 100,
                    ],
                ],
            ],
            'autoplay' => [
                'type'        => 'boolean',
                'label'       => 'Autoplay',
                'description' => 'Automatically play the carousel',
                'default'     => true,
            ],
            'show_indicators' => [
                'type'        => 'boolean',
                'label'       => 'Show Indicators',
                'description' => 'Show carousel indicators',
                'default'     => true,
            ],
            'show_controls' => [
                'type'        => 'boolean',
                'label'       => 'Show Controls',
                'description' => 'Show previous/next controls',
                'default'     => true,
            ],
        ]);

        // Features Grid Schema
        $this->updateBlockSchema('features-grid', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the features section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Our Features',
            ],
            'subtitle' => [
                'type'        => 'textarea',
                'label'       => 'Section Subtitle',
                'description' => 'Subtitle or description for the section',
                'required'    => false,
                'max_length'  => 200,
            ],
            'features' => [
                'type'        => 'table',
                'label'       => 'Features',
                'description' => 'List of features to display',
                'max_items'   => 12,
                'fields'      => [
                    'title' => [
                        'type'       => 'text',
                        'label'      => 'Feature Title',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'description' => [
                        'type'       => 'textarea',
                        'label'      => 'Feature Description',
                        'required'   => true,
                        'max_length' => 300,
                    ],
                    'icon' => [
                        'type'        => 'text',
                        'label'       => 'Icon Class',
                        'description' => 'CSS class for the feature icon (e.g., bi-star-fill)',
                        'required'    => false,
                        'max_length'  => 50,
                    ],
                    'icon_color' => [
                        'type'     => 'select',
                        'label'    => 'Icon Color',
                        'required' => false,
                        'options'  => [
                            ['value' => 'text-primary', 'label' => 'Primary'],
                            ['value' => 'text-success', 'label' => 'Success'],
                            ['value' => 'text-warning', 'label' => 'Warning'],
                            ['value' => 'text-danger', 'label' => 'Danger'],
                            ['value' => 'text-info', 'label' => 'Info'],
                        ],
                        'default' => 'text-primary',
                    ],
                ],
            ],
            'columns' => [
                'type'        => 'select',
                'label'       => 'Grid Columns',
                'description' => 'Number of columns in the grid',
                'required'    => true,
                'options'     => [
                    ['value' => '2', 'label' => '2 Columns'],
                    ['value' => '3', 'label' => '3 Columns'],
                    ['value' => '4', 'label' => '4 Columns'],
                ],
                'default' => '3',
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Section background color',
                'required'    => false,
                'options'     => [
                    ['value' => 'bg-light', 'label' => 'Light'],
                    ['value' => 'bg-white', 'label' => 'White'],
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                    ['value' => 'bg-secondary', 'label' => 'Secondary'],
                ],
                'default' => 'bg-light',
            ],
        ]);

        // Specifications Schema
        $this->updateBlockSchema('specifications', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the specifications section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Product Specifications',
            ],
            'specifications' => [
                'type'        => 'table',
                'label'       => 'Specifications',
                'description' => 'Product specifications list',
                'max_items'   => 20,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Specification Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'value' => [
                        'type'       => 'text',
                        'label'      => 'Specification Value',
                        'required'   => true,
                        'max_length' => 200,
                    ],
                    'unit' => [
                        'type'        => 'text',
                        'label'       => 'Unit',
                        'description' => 'Unit of measurement (optional)',
                        'required'    => false,
                        'max_length'  => 20,
                    ],
                ],
            ],
            'table_style' => [
                'type'        => 'select',
                'label'       => 'Table Style',
                'description' => 'Choose the table styling',
                'required'    => true,
                'options'     => [
                    ['value' => 'table-striped', 'label' => 'Striped'],
                    ['value' => 'table-bordered', 'label' => 'Bordered'],
                    ['value' => 'table-hover', 'label' => 'Hover'],
                    ['value' => 'table-striped table-hover', 'label' => 'Striped + Hover'],
                ],
                'default' => 'table-striped table-hover',
            ],
        ]);

        // Video Showcase Schema
        $this->updateBlockSchema('video-showcase', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the video section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Watch Our Story',
            ],
            'description' => [
                'type'        => 'textarea',
                'label'       => 'Description',
                'description' => 'Description text for the video section',
                'required'    => false,
                'max_length'  => 500,
            ],
            'video_url' => [
                'type'        => 'text',
                'label'       => 'Video URL',
                'description' => 'URL to the video file',
                'required'    => true,
            ],
            'video_poster' => [
                'type'        => 'image',
                'label'       => 'Video Poster',
                'description' => 'Poster image for the video',
                'required'    => false,
                'max_size'    => '5MB',
            ],
            'cta_buttons' => [
                'type'        => 'table',
                'label'       => 'Call to Action Buttons',
                'description' => 'Buttons to display with the video',
                'max_items'   => 3,
                'fields'      => [
                    'text' => [
                        'type'       => 'text',
                        'label'      => 'Button Text',
                        'required'   => true,
                        'max_length' => 50,
                    ],
                    'url' => [
                        'type'     => 'text',
                        'label'    => 'Button URL',
                        'required' => true,
                    ],
                    'style' => [
                        'type'     => 'select',
                        'label'    => 'Button Style',
                        'required' => true,
                        'options'  => [
                            ['value' => 'btn-primary', 'label' => 'Primary'],
                            ['value' => 'btn-outline-secondary', 'label' => 'Outline Secondary'],
                            ['value' => 'btn-secondary', 'label' => 'Secondary'],
                        ],
                        'default' => 'btn-primary',
                    ],
                ],
            ],
            'layout' => [
                'type'        => 'select',
                'label'       => 'Layout',
                'description' => 'Choose the layout style',
                'required'    => true,
                'options'     => [
                    ['value' => 'video-left', 'label' => 'Video on Left'],
                    ['value' => 'video-right', 'label' => 'Video on Right'],
                ],
                'default' => 'video-right',
            ],
        ]);

        // Photo Gallery Schema
        $this->updateBlockSchema('photo-gallery', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Gallery Title',
                'description' => 'Title for the photo gallery',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Photo Gallery',
            ],
            'description' => [
                'type'        => 'textarea',
                'label'       => 'Gallery Description',
                'description' => 'Description for the photo gallery',
                'required'    => false,
                'max_length'  => 200,
            ],
            'photos' => [
                'type'        => 'table',
                'label'       => 'Photos',
                'description' => 'Photos in the gallery',
                'max_items'   => 20,
                'fields'      => [
                    'image' => [
                        'type'     => 'image',
                        'label'    => 'Photo',
                        'required' => true,
                        'max_size' => '5MB',
                    ],
                    'title' => [
                        'type'       => 'text',
                        'label'      => 'Photo Title',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                    'description' => [
                        'type'       => 'textarea',
                        'label'      => 'Photo Description',
                        'required'   => false,
                        'max_length' => 200,
                    ],
                    'alt_text' => [
                        'type'        => 'text',
                        'label'       => 'Alt Text',
                        'description' => 'Alternative text for accessibility',
                        'required'    => false,
                        'max_length'  => 100,
                    ],
                ],
            ],
            'columns' => [
                'type'        => 'select',
                'label'       => 'Gallery Columns',
                'description' => 'Number of columns in the gallery',
                'required'    => true,
                'options'     => [
                    ['value' => '2', 'label' => '2 Columns'],
                    ['value' => '3', 'label' => '3 Columns'],
                    ['value' => '4', 'label' => '4 Columns'],
                    ['value' => '6', 'label' => '6 Columns'],
                ],
                'default' => '3',
            ],
            'lightbox' => [
                'type'        => 'boolean',
                'label'       => 'Enable Lightbox',
                'description' => 'Enable lightbox for photo viewing',
                'default'     => true,
            ],
        ]);

        // Customer Reviews Schema
        $this->updateBlockSchema('customer-reviews', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the reviews section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'What Our Customers Say',
            ],
            'reviews' => [
                'type'        => 'table',
                'label'       => 'Customer Reviews',
                'description' => 'Customer reviews and testimonials',
                'max_items'   => 10,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Customer Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'company' => [
                        'type'       => 'text',
                        'label'      => 'Company',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                    'content' => [
                        'type'       => 'textarea',
                        'label'      => 'Review Content',
                        'required'   => true,
                        'max_length' => 500,
                    ],
                    'rating' => [
                        'type'        => 'number',
                        'label'       => 'Rating',
                        'description' => 'Rating from 1 to 5',
                        'required'    => false,
                        'min'         => 1,
                        'max'         => 5,
                        'default'     => 5,
                    ],
                    'avatar' => [
                        'type'     => 'image',
                        'label'    => 'Customer Avatar',
                        'required' => false,
                        'max_size' => '1MB',
                    ],
                ],
            ],
            'display_style' => [
                'type'        => 'select',
                'label'       => 'Display Style',
                'description' => 'How to display the reviews',
                'required'    => true,
                'options'     => [
                    ['value' => 'grid', 'label' => 'Grid Layout'],
                    ['value' => 'carousel', 'label' => 'Carousel/Slider'],
                    ['value' => 'list', 'label' => 'List Layout'],
                ],
                'default' => 'grid',
            ],
        ]);

        // Testimonials Schema
        $this->updateBlockSchema('testimonials', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for testimonials section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Customer Testimonials',
            ],
            'testimonials' => [
                'type'        => 'table',
                'label'       => 'Testimonials',
                'description' => 'Customer testimonials',
                'max_items'   => 10,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Customer Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'position' => [
                        'type'       => 'text',
                        'label'      => 'Position/Company',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                    'content' => [
                        'type'       => 'textarea',
                        'label'      => 'Testimonial Content',
                        'required'   => true,
                        'max_length' => 500,
                    ],
                    'avatar' => [
                        'type'     => 'image',
                        'label'    => 'Customer Avatar',
                        'required' => false,
                        'max_size' => '1MB',
                    ],
                    'color_theme' => [
                        'type'        => 'select',
                        'label'       => 'Color Theme',
                        'description' => 'Color theme for this testimonial',
                        'required'    => false,
                        'options'     => [
                            ['value' => 'bg-primary', 'label' => 'Primary'],
                            ['value' => 'bg-success', 'label' => 'Success'],
                            ['value' => 'bg-warning', 'label' => 'Warning'],
                            ['value' => 'bg-info', 'label' => 'Info'],
                        ],
                        'default' => 'bg-primary',
                    ],
                ],
            ],
        ]);

        // Partners Logos Schema
        $this->updateBlockSchema('partners-logos', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the partners section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Trusted by Industry Leaders',
            ],
            'partners' => [
                'type'        => 'table',
                'label'       => 'Partners',
                'description' => 'Partner company logos',
                'max_items'   => 12,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Partner Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'logo' => [
                        'type'     => 'image',
                        'label'    => 'Partner Logo',
                        'required' => true,
                        'max_size' => '2MB',
                    ],
                    'url' => [
                        'type'     => 'url',
                        'label'    => 'Partner Website',
                        'required' => false,
                    ],
                ],
            ],
            'columns' => [
                'type'        => 'select',
                'label'       => 'Logo Columns',
                'description' => 'Number of columns for logos',
                'required'    => true,
                'options'     => [
                    ['value' => '3', 'label' => '3 Columns'],
                    ['value' => '4', 'label' => '4 Columns'],
                    ['value' => '6', 'label' => '6 Columns'],
                ],
                'default' => '4',
            ],
        ]);

        // Achievement Stats Schema
        $this->updateBlockSchema('achievement-stats', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the stats section (optional)',
                'required'    => false,
                'max_length'  => 100,
            ],
            'stats' => [
                'type'        => 'table',
                'label'       => 'Statistics',
                'description' => 'Achievement statistics',
                'max_items'   => 8,
                'fields'      => [
                    'number' => [
                        'type'        => 'text',
                        'label'       => 'Statistic Number',
                        'description' => 'The number to display (e.g., 10K+, 99.9%)',
                        'required'    => true,
                        'max_length'  => 20,
                    ],
                    'label' => [
                        'type'        => 'text',
                        'label'       => 'Statistic Label',
                        'description' => 'Description of the statistic',
                        'required'    => true,
                        'max_length'  => 100,
                    ],
                    'icon' => [
                        'type'        => 'text',
                        'label'       => 'Icon Class',
                        'description' => 'CSS class for icon (optional)',
                        'required'    => false,
                        'max_length'  => 50,
                    ],
                ],
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Section background color',
                'required'    => true,
                'options'     => [
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                    ['value' => 'bg-secondary', 'label' => 'Secondary'],
                    ['value' => 'bg-success', 'label' => 'Success'],
                    ['value' => 'bg-dark', 'label' => 'Dark'],
                ],
                'default' => 'bg-primary',
            ],
            'text_color' => [
                'type'        => 'select',
                'label'       => 'Text Color',
                'description' => 'Text color for the section',
                'required'    => true,
                'options'     => [
                    ['value' => 'text-white', 'label' => 'White'],
                    ['value' => 'text-dark', 'label' => 'Dark'],
                ],
                'default' => 'text-white',
            ],
        ]);

        // Team Showcase Schema
        $this->updateBlockSchema('team-showcase', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the team section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Meet Our Team',
            ],
            'team_members' => [
                'type'        => 'table',
                'label'       => 'Team Members',
                'description' => 'Team member information',
                'max_items'   => 12,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Member Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'position' => [
                        'type'       => 'text',
                        'label'      => 'Position',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'bio' => [
                        'type'        => 'textarea',
                        'label'       => 'Bio',
                        'description' => 'Short biography',
                        'required'    => false,
                        'max_length'  => 300,
                    ],
                    'photo' => [
                        'type'     => 'image',
                        'label'    => 'Member Photo',
                        'required' => false,
                        'max_size' => '2MB',
                    ],
                    'social_links' => [
                        'type'        => 'object',
                        'label'       => 'Social Links',
                        'description' => 'Social media links',
                        'required'    => false,
                        'fields'      => [
                            'linkedin' => [
                                'type'     => 'url',
                                'label'    => 'LinkedIn',
                                'required' => false,
                            ],
                            'twitter' => [
                                'type'     => 'url',
                                'label'    => 'Twitter',
                                'required' => false,
                            ],
                            'github' => [
                                'type'     => 'url',
                                'label'    => 'GitHub',
                                'required' => false,
                            ],
                        ],
                    ],
                ],
            ],
            'columns' => [
                'type'        => 'select',
                'label'       => 'Team Columns',
                'description' => 'Number of columns for team members',
                'required'    => true,
                'options'     => [
                    ['value' => '3', 'label' => '3 Columns'],
                    ['value' => '4', 'label' => '4 Columns'],
                    ['value' => '6', 'label' => '6 Columns'],
                ],
                'default' => '3',
            ],
        ]);

        // Company History Schema
        $this->updateBlockSchema('company-history', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the history section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Our Journey',
            ],
            'timeline_events' => [
                'type'        => 'table',
                'label'       => 'Timeline Events',
                'description' => 'Company history timeline events',
                'max_items'   => 10,
                'fields'      => [
                    'year' => [
                        'type'       => 'text',
                        'label'      => 'Year',
                        'required'   => true,
                        'max_length' => 10,
                    ],
                    'title' => [
                        'type'       => 'text',
                        'label'      => 'Event Title',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'description' => [
                        'type'       => 'textarea',
                        'label'      => 'Event Description',
                        'required'   => true,
                        'max_length' => 300,
                    ],
                    'color' => [
                        'type'        => 'select',
                        'label'       => 'Event Color',
                        'description' => 'Color theme for this event',
                        'required'    => false,
                        'options'     => [
                            ['value' => 'bg-primary', 'label' => 'Primary'],
                            ['value' => 'bg-success', 'label' => 'Success'],
                            ['value' => 'bg-warning', 'label' => 'Warning'],
                            ['value' => 'bg-info', 'label' => 'Info'],
                        ],
                        'default' => 'bg-primary',
                    ],
                ],
            ],
        ]);

        // Social Media Feed Schema
        $this->updateBlockSchema('social-media-feed', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the social media section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Follow Us on Social Media',
            ],
            'social_posts' => [
                'type'        => 'table',
                'label'       => 'Social Media Posts',
                'description' => 'Social media posts to display',
                'max_items'   => 10,
                'fields'      => [
                    'platform' => [
                        'type'     => 'select',
                        'label'    => 'Platform',
                        'required' => true,
                        'options'  => [
                            ['value' => 'instagram', 'label' => 'Instagram'],
                            ['value' => 'twitter', 'label' => 'Twitter'],
                            ['value' => 'linkedin', 'label' => 'LinkedIn'],
                            ['value' => 'facebook', 'label' => 'Facebook'],
                            ['value' => 'youtube', 'label' => 'YouTube'],
                        ],
                    ],
                    'content' => [
                        'type'       => 'textarea',
                        'label'      => 'Post Content',
                        'required'   => true,
                        'max_length' => 300,
                    ],
                    'timestamp' => [
                        'type'        => 'text',
                        'label'       => 'Post Time',
                        'description' => 'When the post was made (e.g., "2 hours ago")',
                        'required'    => true,
                        'max_length'  => 50,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Post URL',
                        'description' => 'Link to the original post',
                        'required'    => false,
                    ],
                ],
            ],
        ]);

        // Blog Posts Schema
        $this->updateBlockSchema('blog-posts', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the blog section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Latest from Our Blog',
            ],
            'posts' => [
                'type'        => 'table',
                'label'       => 'Blog Posts',
                'description' => 'Blog posts to display',
                'max_items'   => 6,
                'fields'      => [
                    'title' => [
                        'type'       => 'text',
                        'label'      => 'Post Title',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'excerpt' => [
                        'type'        => 'textarea',
                        'label'       => 'Post Excerpt',
                        'description' => 'Short summary of the post',
                        'required'    => true,
                        'max_length'  => 200,
                    ],
                    'featured_image' => [
                        'type'     => 'image',
                        'label'    => 'Featured Image',
                        'required' => false,
                        'max_size' => '2MB',
                    ],
                    'author' => [
                        'type'       => 'text',
                        'label'      => 'Author',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                    'publish_date' => [
                        'type'     => 'date',
                        'label'    => 'Publish Date',
                        'required' => false,
                    ],
                    'url' => [
                        'type'        => 'url',
                        'label'       => 'Post URL',
                        'description' => 'Link to the full post',
                        'required'    => true,
                    ],
                ],
            ],
            'columns' => [
                'type'        => 'select',
                'label'       => 'Post Columns',
                'description' => 'Number of columns for blog posts',
                'required'    => true,
                'options'     => [
                    ['value' => '2', 'label' => '2 Columns'],
                    ['value' => '3', 'label' => '3 Columns'],
                    ['value' => '4', 'label' => '4 Columns'],
                ],
                'default' => '3',
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Section background color',
                'required'    => false,
                'options'     => [
                    ['value' => 'bg-light', 'label' => 'Light'],
                    ['value' => 'bg-white', 'label' => 'White'],
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                ],
                'default' => 'bg-light',
            ],
        ]);

        // FAQ Section Schema
        $this->updateBlockSchema('faq-section', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the FAQ section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Frequently Asked Questions',
            ],
            'faqs' => [
                'type'        => 'table',
                'label'       => 'FAQ Items',
                'description' => 'Frequently asked questions and answers',
                'max_items'   => 20,
                'fields'      => [
                    'question' => [
                        'type'       => 'text',
                        'label'      => 'Question',
                        'required'   => true,
                        'max_length' => 200,
                    ],
                    'answer' => [
                        'type'       => 'textarea',
                        'label'      => 'Answer',
                        'required'   => true,
                        'max_length' => 1000,
                    ],
                    'category' => [
                        'type'        => 'text',
                        'label'       => 'Category',
                        'description' => 'FAQ category (optional)',
                        'required'    => false,
                        'max_length'  => 50,
                    ],
                ],
            ],
            'accordion_style' => [
                'type'        => 'select',
                'label'       => 'Accordion Style',
                'description' => 'Choose the accordion styling',
                'required'    => true,
                'options'     => [
                    ['value' => 'default', 'label' => 'Default'],
                    ['value' => 'bordered', 'label' => 'Bordered'],
                    ['value' => 'shadow', 'label' => 'Shadow'],
                ],
                'default' => 'default',
            ],
        ]);

        // Pricing Plans Schema
        $this->updateBlockSchema('pricing-plans', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the pricing section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Choose Your Plan',
            ],
            'plans' => [
                'type'        => 'table',
                'label'       => 'Pricing Plans',
                'description' => 'Pricing plans to display',
                'max_items'   => 5,
                'fields'      => [
                    'name' => [
                        'type'       => 'text',
                        'label'      => 'Plan Name',
                        'required'   => true,
                        'max_length' => 100,
                    ],
                    'price' => [
                        'type'        => 'text',
                        'label'       => 'Price',
                        'description' => 'Price amount (e.g., $9, $29)',
                        'required'    => true,
                        'max_length'  => 20,
                    ],
                    'period' => [
                        'type'        => 'text',
                        'label'       => 'Billing Period',
                        'description' => 'Billing period (e.g., /month, /year)',
                        'required'    => true,
                        'max_length'  => 20,
                    ],
                    'features' => [
                        'type'        => 'table',
                        'label'       => 'Plan Features',
                        'description' => 'Features included in this plan',
                        'max_items'   => 20,
                        'fields'      => [
                            'feature' => [
                                'type'       => 'text',
                                'label'      => 'Feature',
                                'required'   => true,
                                'max_length' => 100,
                            ],
                            'included' => [
                                'type'        => 'boolean',
                                'label'       => 'Included',
                                'description' => 'Whether this feature is included',
                                'default'     => true,
                            ],
                        ],
                    ],
                    'cta_text' => [
                        'type'       => 'text',
                        'label'      => 'CTA Button Text',
                        'required'   => true,
                        'max_length' => 50,
                        'default'    => 'Get Started',
                    ],
                    'cta_url' => [
                        'type'     => 'url',
                        'label'    => 'CTA Button URL',
                        'required' => true,
                    ],
                    'popular' => [
                        'type'        => 'boolean',
                        'label'       => 'Popular Plan',
                        'description' => 'Mark as most popular plan',
                        'default'     => false,
                    ],
                ],
            ],
            'background_color' => [
                'type'        => 'select',
                'label'       => 'Background Color',
                'description' => 'Section background color',
                'required'    => false,
                'options'     => [
                    ['value' => 'bg-light', 'label' => 'Light'],
                    ['value' => 'bg-white', 'label' => 'White'],
                    ['value' => 'bg-primary', 'label' => 'Primary'],
                ],
                'default' => 'bg-light',
            ],
        ]);

        // Location Map Schema
        $this->updateBlockSchema('location-map', [
            'title' => [
                'type'        => 'text',
                'label'       => 'Section Title',
                'description' => 'Title for the location section',
                'required'    => true,
                'max_length'  => 100,
                'default'     => 'Visit Our Office',
            ],
            'map_embed_url' => [
                'type'        => 'text',
                'label'       => 'Map Embed URL',
                'description' => 'Google Maps embed URL',
                'required'    => true,
            ],
            'contact_info' => [
                'type'        => 'object',
                'label'       => 'Contact Information',
                'description' => 'Contact details to display',
                'fields'      => [
                    'address' => [
                        'type'       => 'text',
                        'label'      => 'Address',
                        'required'   => true,
                        'max_length' => 200,
                    ],
                    'phone' => [
                        'type'       => 'text',
                        'label'      => 'Phone Number',
                        'required'   => false,
                        'max_length' => 50,
                    ],
                    'email' => [
                        'type'     => 'email',
                        'label'    => 'Email Address',
                        'required' => false,
                    ],
                    'hours' => [
                        'type'       => 'text',
                        'label'      => 'Business Hours',
                        'required'   => false,
                        'max_length' => 100,
                    ],
                ],
            ],
            'description' => [
                'type'        => 'textarea',
                'label'       => 'Description',
                'description' => 'Additional information about the location',
                'required'    => false,
                'max_length'  => 300,
            ],
        ]);
    }

    private function updateBlockSchema(string $reference, array $schema): void
    {
        $block = PageBlock::where('reference', $reference)->first();

        if ($block) {
            $block->update(['schema' => $schema]);
            $this->command->info("Updated schema for block: {$reference}");
        } else {
            $this->command->warn("Block not found: {$reference}");
        }
    }
}
