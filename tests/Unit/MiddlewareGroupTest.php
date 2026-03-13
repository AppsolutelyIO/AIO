<?php

namespace Appsolutely\AIO\Tests\Unit;

use Appsolutely\AIO\AdminServiceProvider;
use ReflectionClass;

class MiddlewareGroupTest extends TestCase
{
    private function getMiddlewareGroups(): array
    {
        $rc = new ReflectionClass(AdminServiceProvider::class);
        $prop = $rc->getProperty('middlewareGroups');

        return $prop->getDefaultValue();
    }

    public function test_admin_group_does_not_contain_pjax()
    {
        $groups = $this->getMiddlewareGroups();

        $this->assertArrayHasKey('admin', $groups);
        $this->assertNotContains(
            'admin.pjax',
            $groups['admin'],
            'admin.pjax should not be in the global admin middleware group — it only applies to page routes'
        );
    }

    public function test_admin_group_does_not_contain_upload()
    {
        $groups = $this->getMiddlewareGroups();

        $this->assertNotContains(
            'admin.upload',
            $groups['admin'],
            'admin.upload should not be in the global admin middleware group — it only applies to upload routes'
        );
    }

    public function test_admin_group_still_contains_core_middleware()
    {
        $groups = $this->getMiddlewareGroups();

        $this->assertContains('admin.auth', $groups['admin']);
        $this->assertContains('admin.bootstrap', $groups['admin']);
        $this->assertContains('admin.permission', $groups['admin']);
        $this->assertContains('admin.session', $groups['admin']);
    }
}
