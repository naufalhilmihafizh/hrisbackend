<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $manager;
    protected User $admin;
    protected string $employeeToken;
    protected string $managerToken;
    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'HR Administrator',
            'email' => 'admin@hris.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->adminToken = $this->admin->createToken('test_token')->plainTextToken;

        $this->manager = User::create([
            'name' => 'IT Manager',
            'email' => 'manager@hris.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);
        $this->managerToken = $this->manager->createToken('test_token')->plainTextToken;

        $this->employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@hris.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'manager_id' => $this->manager->id,
            'is_active' => true,
        ]);
        $this->employeeToken = $this->employee->createToken('test_token')->plainTextToken;
    }

    /**
     * Test employee dashboard metrics retrieval.
     */
    public function test_employee_dashboard_stats(): void
    {
        $response = $this->getJson('/api/dashboard', [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'today_attendance',
                    'remaining_leave_days',
                    'pending_requests_count',
                    'latest_payroll',
                ]
            ]);
    }

    /**
     * Test manager dashboard metrics retrieval.
     */
    public function test_manager_dashboard_stats(): void
    {
        $response = $this->getJson('/api/dashboard', [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'subordinates_count',
                    'present_today_count',
                    'pending_approvals_count',
                    'team_attendance_rate',
                ]
            ]);
    }

    /**
     * Test admin dashboard metrics retrieval.
     */
    public function test_admin_dashboard_stats(): void
    {
        $response = $this->getJson('/api/dashboard', [
            'Authorization' => 'Bearer ' . $this->adminToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'active_employees_count',
                    'today_attendance' => [
                        'present',
                        'late',
                        'absent',
                    ],
                    'pending_approvals_count',
                    'current_month_payroll_total',
                ]
            ]);
    }
}
