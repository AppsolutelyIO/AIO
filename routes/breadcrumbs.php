<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

if (! Breadcrumbs::exists('home')) {
    Breadcrumbs::for('home', function (BreadcrumbTrail $trail) {
        $trail->push('Home', route('home'));
    });
}

if (! Breadcrumbs::exists('page')) {
    Breadcrumbs::for('page', function (BreadcrumbTrail $trail, $page) {
        $trail->parent('home');

        collect($page['ancestors'] ?? [])
            ->filter(fn ($a) => ! empty($a['title']) && ! empty($a['slug']))
            ->each(fn ($a) => $trail->push($a['title'], route('pages.show', $a['slug'])));

        if (! empty($page['title']) && ! empty($page['slug'])) {
            $trail->push($page['title'], route('pages.show', $page['slug']));
        }
    });
}
