<?php

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Admin;

class RouteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Admin::routes();
    }

    // --- Auth routes exist ---

    public function test_login_post_route_exists()
    {
        $response = $this->post('/admin/auth/login', [
            'username' => 'nonexistent',
            'password' => 'wrong',
        ]);

        // Should not be 404
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    public function test_logout_route_exists()
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/auth/logout');

        // Should redirect, not 404
        $this->assertNotEquals(404, $response->getStatusCode());
    }

    // --- API routes ---

    public function test_api_routes_registered()
    {
        $routes = app('router')->getRoutes();

        $postRoutes = [
            'admin/api/action',
            'admin/api/form',
            'admin/api/form/upload',
            'admin/api/form/destroy-file',
            'admin/api/value',
            'admin/api/inline-update',
            'admin/api/tinymce/upload',
            'admin/api/editor-md/upload',
            'admin/api/vditor/upload',
        ];

        foreach ($postRoutes as $uri) {
            $matched = $routes->match(
                \Illuminate\Http\Request::create($uri, 'POST')
            );
            $this->assertNotNull($matched, "POST API route [{$uri}] should be registered");
        }

        // render is GET
        $renderRoute = $routes->match(
            \Illuminate\Http\Request::create('admin/api/render', 'GET')
        );
        $this->assertNotNull($renderRoute, "GET API route [admin/api/render] should be registered");
    }

    // --- Route prefix ---

    public function test_route_prefix_is_admin()
    {
        $this->assertSame('admin', config('admin.route.prefix'));
    }

    // --- API route names ---

    public function test_api_route_names_contain_api()
    {
        $routes = app('router')->getRoutes();
        $apiNames = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name && str_contains($name, 'api.')) {
                $apiNames[] = $name;
            }
        }

        $this->assertNotEmpty($apiNames, 'Should have routes with api. in their name');

        // Check key routes exist
        $hasAction = false;
        $hasForm = false;
        foreach ($apiNames as $name) {
            if (str_ends_with($name, 'api.action')) {
                $hasAction = true;
            }
            if (str_ends_with($name, 'api.form')) {
                $hasForm = true;
            }
        }
        $this->assertTrue($hasAction, 'Should have an api.action route');
        $this->assertTrue($hasForm, 'Should have an api.form route');
    }

    // --- No dcat routes ---

    public function test_no_dcat_api_prefix()
    {
        $routes = app('router')->getRoutes();

        foreach ($routes as $route) {
            $uri = $route->uri();
            $this->assertStringNotContainsString('dcat-api', $uri, "Route URI should not contain 'dcat-api': {$uri}");
        }
    }

    // --- Auth resource routes ---

    public function test_auth_user_resource_routes()
    {
        $routes = app('router')->getRoutes();

        $matched = $routes->match(\Illuminate\Http\Request::create('/admin/auth/users', 'GET'));
        $this->assertNotNull($matched, 'GET /admin/auth/users route should exist');
    }

    public function test_auth_menu_resource_routes()
    {
        $routes = app('router')->getRoutes();

        $matched = $routes->match(\Illuminate\Http\Request::create('/admin/auth/menu', 'GET'));
        $this->assertNotNull($matched, 'GET /admin/auth/menu route should exist');
    }
}
