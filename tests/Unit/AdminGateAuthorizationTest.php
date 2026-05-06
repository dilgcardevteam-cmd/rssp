<?php

namespace Tests\Unit;

use App\Models\Admin;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminGateAuthorizationTest extends TestCase
{
    public function test_viewer_has_exam_monitor_only_permissions(): void
    {
        $viewer = new Admin(['role' => 'viewer']);

        $this->assertTrue(Gate::forUser($viewer)->allows('admin.exam.monitor'));
        $this->assertFalse(Gate::forUser($viewer)->allows('admin.exam.manage'));
        $this->assertFalse(Gate::forUser($viewer)->allows('admin.applicants.monitor'));
        $this->assertFalse(Gate::forUser($viewer)->allows('admin.system.manage'));
    }

    public function test_hr_division_has_applicant_monitor_permissions(): void
    {
        $hr = new Admin(['role' => 'hr_division']);

        $this->assertFalse(Gate::forUser($hr)->allows('admin.exam.monitor'));
        $this->assertFalse(Gate::forUser($hr)->allows('admin.exam.manage'));
        $this->assertTrue(Gate::forUser($hr)->allows('admin.applicants.monitor'));
        $this->assertFalse(Gate::forUser($hr)->allows('admin.system.manage'));
    }

    public function test_admin_and_superadmin_have_expected_permissions(): void
    {
        $admin = new Admin(['role' => 'admin']);
        $superadmin = new Admin(['role' => 'superadmin']);

        $this->assertTrue(Gate::forUser($admin)->allows('admin.exam.monitor'));
        $this->assertTrue(Gate::forUser($admin)->allows('admin.exam.manage'));
        $this->assertTrue(Gate::forUser($admin)->allows('admin.applicants.monitor'));
        $this->assertFalse(Gate::forUser($admin)->allows('admin.system.manage'));

        $this->assertTrue(Gate::forUser($superadmin)->allows('admin.exam.monitor'));
        $this->assertTrue(Gate::forUser($superadmin)->allows('admin.exam.manage'));
        $this->assertTrue(Gate::forUser($superadmin)->allows('admin.applicants.monitor'));
        $this->assertTrue(Gate::forUser($superadmin)->allows('admin.system.manage'));
    }
}

