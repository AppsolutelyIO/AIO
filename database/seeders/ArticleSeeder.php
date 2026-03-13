<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Seeders;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Appsolutely\AIO\Models\ArticleCategory;
use Illuminate\Database\Seeder;

final class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $this->createCategories();

        // Create articles
        $this->createArticles();
    }

    private function createCategories(): void
    {
        $categories = [
            [
                'title'        => 'Technology',
                'slug'         => 'technology',
                'description'  => 'Latest technology news, trends, and insights',
                'keywords'     => 'technology, tech, innovation, digital, software',
                'status'       => Status::ACTIVE,
                'published_at' => now(),
            ],
            [
                'title'        => 'Business',
                'slug'         => 'business',
                'description'  => 'Business strategies, entrepreneurship, and market analysis',
                'keywords'     => 'business, entrepreneurship, strategy, market, finance',
                'status'       => Status::ACTIVE,
                'published_at' => now(),
            ],
        ];

        foreach ($categories as $categoryData) {
            ArticleCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }

    private function createArticles(): void
    {
        $technologyCategory = ArticleCategory::where('slug', 'technology')->first();
        $businessCategory   = ArticleCategory::where('slug', 'business')->first();

        if ($technologyCategory) {
            $this->createArticlesFromArray($this->getTechnologyArticles(), $technologyCategory);
        }

        if ($businessCategory) {
            $this->createArticlesFromArray($this->getBusinessArticles(), $businessCategory);
        }
    }

    private function createArticlesFromArray(array $articles, ArticleCategory $category): void
    {
        foreach ($articles as $articleData) {
            $article = Article::firstOrCreate(
                ['slug' => $articleData['slug']],
                $articleData
            );

            // Attach to category
            $article->categories()->syncWithoutDetaching([$category->id]);
        }
    }

    private function getTechnologyArticles(): array
    {
        return [
            [
                'title'        => 'The Future of Artificial Intelligence in 2024',
                'slug'         => 'future-of-artificial-intelligence-2024',
                'description'  => 'Exploring the latest developments in AI and machine learning technologies.',
                'content'      => $this->getTechnologyContent('AI'),
                'keywords'     => 'artificial intelligence, AI, machine learning, technology',
                'cover'        => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Web Development Trends You Need to Know',
                'slug'         => 'web-development-trends-2024',
                'description'  => 'Discover the most important web development trends shaping the industry.',
                'content'      => $this->getTechnologyContent('Web Development'),
                'keywords'     => 'web development, programming, trends, frontend, backend',
                'cover'        => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Cybersecurity Best Practices for Small Businesses',
                'slug'         => 'cybersecurity-best-practices-small-businesses',
                'description'  => 'Essential cybersecurity measures every small business should implement.',
                'content'      => $this->getTechnologyContent('Cybersecurity'),
                'keywords'     => 'cybersecurity, security, small business, protection',
                'cover'        => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Cloud Computing: Benefits and Challenges',
                'slug'         => 'cloud-computing-benefits-challenges',
                'description'  => 'Understanding the advantages and potential drawbacks of cloud computing.',
                'content'      => $this->getTechnologyContent('Cloud Computing'),
                'keywords'     => 'cloud computing, AWS, Azure, Google Cloud, infrastructure',
                'cover'        => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Mobile App Development: Native vs Cross-Platform',
                'slug'         => 'mobile-app-development-native-vs-cross-platform',
                'description'  => 'Comparing native and cross-platform approaches to mobile app development.',
                'content'      => $this->getTechnologyContent('Mobile Development'),
                'keywords'     => 'mobile development, React Native, Flutter, iOS, Android',
                'cover'        => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Data Science and Analytics: A Complete Guide',
                'slug'         => 'data-science-analytics-complete-guide',
                'description'  => 'Comprehensive guide to data science and analytics fundamentals.',
                'content'      => $this->getTechnologyContent('Data Science'),
                'keywords'     => 'data science, analytics, big data, machine learning',
                'cover'        => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Blockchain Technology: Beyond Cryptocurrency',
                'slug'         => 'blockchain-technology-beyond-cryptocurrency',
                'description'  => 'Exploring blockchain applications beyond digital currencies.',
                'content'      => $this->getTechnologyContent('Blockchain'),
                'keywords'     => 'blockchain, cryptocurrency, distributed ledger, smart contracts',
                'cover'        => 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Internet of Things (IoT) Revolution',
                'slug'         => 'internet-of-things-iot-revolution',
                'description'  => 'How IoT is transforming industries and daily life.',
                'content'      => $this->getTechnologyContent('IoT'),
                'keywords'     => 'IoT, internet of things, smart devices, connectivity',
                'cover'        => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'DevOps: Bridging Development and Operations',
                'slug'         => 'devops-bridging-development-operations',
                'description'  => 'Understanding DevOps principles and practices.',
                'content'      => $this->getTechnologyContent('DevOps'),
                'keywords'     => 'devops, CI/CD, automation, deployment, infrastructure',
                'cover'        => 'https://images.unsplash.com/photo-1667372393119-3d4c48d07fc9?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Virtual Reality and Augmented Reality Trends',
                'slug'         => 'virtual-reality-augmented-reality-trends',
                'description'  => 'Latest developments in VR and AR technologies.',
                'content'      => $this->getTechnologyContent('VR/AR'),
                'keywords'     => 'virtual reality, augmented reality, VR, AR, immersive technology',
                'cover'        => 'https://images.unsplash.com/photo-1593508512255-86ab42a8e620?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
        ];
    }

    private function getBusinessArticles(): array
    {
        return [
            [
                'title'        => 'Startup Success: Building a Strong Foundation',
                'slug'         => 'startup-success-building-strong-foundation',
                'description'  => 'Essential strategies for building a successful startup from the ground up.',
                'content'      => $this->getBusinessContent('Startup'),
                'keywords'     => 'startup, entrepreneurship, business strategy, success',
                'cover'        => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Digital Marketing Strategies for 2024',
                'slug'         => 'digital-marketing-strategies-2024',
                'description'  => 'Effective digital marketing approaches for the modern business landscape.',
                'content'      => $this->getBusinessContent('Digital Marketing'),
                'keywords'     => 'digital marketing, SEO, social media, content marketing',
                'cover'        => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Financial Planning for Small Businesses',
                'slug'         => 'financial-planning-small-businesses',
                'description'  => 'Key financial planning strategies to ensure business growth and stability.',
                'content'      => $this->getBusinessContent('Financial Planning'),
                'keywords'     => 'financial planning, small business, budgeting, cash flow',
                'cover'        => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Customer Experience: The Key to Business Growth',
                'slug'         => 'customer-experience-key-business-growth',
                'description'  => 'How exceptional customer experience drives business success.',
                'content'      => $this->getBusinessContent('Customer Experience'),
                'keywords'     => 'customer experience, CX, customer service, business growth',
                'cover'        => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Remote Work: Managing Distributed Teams',
                'slug'         => 'remote-work-managing-distributed-teams',
                'description'  => 'Best practices for managing and leading remote teams effectively.',
                'content'      => $this->getBusinessContent('Remote Work'),
                'keywords'     => 'remote work, distributed teams, management, productivity',
                'cover'        => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'E-commerce Trends and Opportunities',
                'slug'         => 'ecommerce-trends-opportunities',
                'description'  => 'Latest trends in e-commerce and opportunities for business growth.',
                'content'      => $this->getBusinessContent('E-commerce'),
                'keywords'     => 'e-commerce, online business, digital commerce, trends',
                'cover'        => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Business Analytics: Making Data-Driven Decisions',
                'slug'         => 'business-analytics-data-driven-decisions',
                'description'  => 'How to use business analytics for informed decision-making.',
                'content'      => $this->getBusinessContent('Business Analytics'),
                'keywords'     => 'business analytics, data analysis, decision making, insights',
                'cover'        => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Leadership Skills for Modern Business',
                'slug'         => 'leadership-skills-modern-business',
                'description'  => 'Essential leadership skills needed in today\'s dynamic business environment.',
                'content'      => $this->getBusinessContent('Leadership'),
                'keywords'     => 'leadership, management, business skills, team building',
                'cover'        => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Supply Chain Management in the Digital Age',
                'slug'         => 'supply-chain-management-digital-age',
                'description'  => 'Modern approaches to supply chain management and optimization.',
                'content'      => $this->getBusinessContent('Supply Chain'),
                'keywords'     => 'supply chain, logistics, operations, digital transformation',
                'cover'        => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
            [
                'title'        => 'Sustainable Business Practices',
                'slug'         => 'sustainable-business-practices',
                'description'  => 'Implementing sustainable practices for long-term business success.',
                'content'      => $this->getBusinessContent('Sustainability'),
                'keywords'     => 'sustainability, green business, corporate responsibility, environment',
                'cover'        => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800',
                'status'       => Status::ACTIVE,
                'published_at' => now()->subDays(rand(1, 30)),
            ],
        ];
    }

    private function getTechnologyContent(string $topic): string
    {
        return "# {$topic}: A Comprehensive Overview

## Introduction

{$topic} is revolutionizing the way we approach modern challenges. This comprehensive guide explores the latest developments, trends, and practical applications in this rapidly evolving field.

## Key Concepts

### Understanding the Basics

The fundamental principles of {$topic} are essential for anyone looking to stay ahead in today's technology landscape. From core concepts to advanced applications, understanding these basics provides a solid foundation for further exploration.

### Current Trends

The landscape of {$topic} is constantly evolving, with new trends emerging regularly. Staying informed about these developments is crucial for professionals and enthusiasts alike.

## Practical Applications

### Real-World Examples

Numerous industries are leveraging {$topic} to drive innovation and efficiency. From healthcare to finance, the applications are diverse and impactful.

### Implementation Strategies

Successfully implementing {$topic} requires careful planning and strategic thinking. This section provides practical guidance for effective deployment.

## Future Outlook

### Emerging Technologies

The future of {$topic} looks promising, with exciting developments on the horizon. Understanding these trends helps prepare for what's coming next.

### Industry Impact

The impact of {$topic} on various industries continues to grow, creating new opportunities and challenges for businesses and professionals.

## Conclusion

As {$topic} continues to evolve, staying informed and adaptable is key to success. The opportunities are vast, and the potential for innovation is limitless.

---

*This article provides a comprehensive overview of {$topic} and its implications for the future of technology and business.*";
    }

    private function getBusinessContent(string $topic): string
    {
        return "# {$topic}: Strategic Insights for Business Success

## Executive Summary

{$topic} represents a critical component of modern business strategy. This comprehensive analysis provides actionable insights for business leaders and entrepreneurs looking to leverage these concepts for organizational growth.

## Strategic Framework

### Core Principles

Understanding the fundamental principles of {$topic} is essential for developing effective business strategies. These core concepts form the foundation for successful implementation and execution.

### Market Analysis

A thorough market analysis reveals the current landscape and opportunities within the {$topic} domain. This understanding is crucial for making informed business decisions.

## Implementation Strategies

### Best Practices

Successful implementation of {$topic} strategies requires adherence to proven best practices. This section outlines the most effective approaches based on industry experience and research.

### Risk Management

Every business initiative carries inherent risks. Understanding and managing these risks is essential for sustainable success in {$topic} implementation.

## Performance Metrics

### Key Performance Indicators

Measuring success in {$topic} requires well-defined KPIs. This section provides guidance on selecting and tracking the most relevant metrics for your business objectives.

### ROI Analysis

Understanding the return on investment for {$topic} initiatives is crucial for business planning and resource allocation.

## Competitive Advantage

### Differentiation Strategies

In today's competitive business environment, differentiation is key. This section explores how {$topic} can be leveraged to create sustainable competitive advantages.

### Innovation Opportunities

{$topic} presents numerous opportunities for innovation and growth. Identifying and capitalizing on these opportunities is essential for long-term business success.

## Conclusion

The strategic importance of {$topic} in modern business cannot be overstated. Organizations that effectively leverage these concepts position themselves for sustainable growth and competitive advantage.

---

*This analysis provides strategic insights into {$topic} and its implications for business success in today's dynamic market environment.*";
    }
}
