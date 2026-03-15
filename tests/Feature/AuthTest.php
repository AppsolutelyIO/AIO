<?php

namespace Appsolutely\AIO\Tests\Feature;

use Appsolutely\AIO\Admin;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Admin::routes();
    }

    // --- Login page ---

    public function test_login_page_renders()
    {
        $response = $this->get('/admin/auth/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_redirected_from_login()
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin/auth/login');

        $response->assertRedirect();
    }

    // --- Login POST ---

    public function test_login_with_valid_credentials()
    {
        $this->seedAdminUser('admin', 'password123');

        $response = $this->post('/admin/auth/login', [
            'username' => 'admin',
            'password' => 'password123',
        ]);

        // Successful login returns a JSON response with status 200
        $this->assertNotEquals(422, $response->getStatusCode());
        $this->assertTrue(Admin::guard()->check());
    }

    public function test_login_with_invalid_credentials()
    {
        $this->seedAdminUser('admin', 'password123');

        $response = $this->post('/admin/auth/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $this->assertFalse(Admin::guard()->check());
    }

    public function test_login_requires_username()
    {
        $response = $this->post('/admin/auth/login', [
            'password' => 'password123',
        ]);

        $this->assertFalse(Admin::guard()->check());
    }

    public function test_login_requires_password()
    {
        $response = $this->post('/admin/auth/login', [
            'username' => 'admin',
        ]);

        $this->assertFalse(Admin::guard()->check());
    }

    // --- Logout ---

    public function test_logout()
    {
        $this->loginAsAdmin();

        $this->assertTrue(Admin::guard()->check());

        $response = $this->get('/admin/auth/logout');

        $response->assertRedirect();
        $this->assertFalse(Admin::guard()->check());
    }

    // --- Guard ---

    public function test_admin_guard_name()
    {
        $guardName = config('admin.auth.guard');

        $this->assertSame('admin', $guardName);
    }

    public function test_guard_returns_stateful_guard()
    {
        $guard = Admin::guard();

        $this->assertInstanceOf(StatefulGuard::class, $guard);
    }

    // --- User model ---

    public function test_admin_user_created_in_database()
    {
        $user = $this->seedAdminUser('testuser', 'secret');

        $this->assertDatabaseHas('admin_users', [
            'username' => 'testuser',
            'name'     => 'Administrator',
        ]);
    }

    public function test_admin_user_password_is_hashed()
    {
        $user = $this->seedAdminUser('admin', 'mypassword');

        $this->assertTrue(Hash::check('mypassword', $user->password));
        $this->assertNotEquals('mypassword', $user->password);
    }

    // --- Settings route ---

    public function test_settings_route_requires_auth()
    {
        $response = $this->get('/admin/auth/setting');

        // Unauthenticated user should not get 200 OK
        $this->assertNotEquals(200, $response->getStatusCode());
    }
}
