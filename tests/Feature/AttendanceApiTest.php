<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $manager;
    protected string $employeeToken;
    protected string $managerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a manager
        $this->manager = User::create([
            'name' => 'IT Manager',
            'email' => 'manager@hris.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
            'is_active' => true,
        ]);
        $this->managerToken = $this->manager->createToken('test_token')->plainTextToken;

        // Create an employee managed by the manager
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
     * Test check-in success.
     */
    public function test_check_in_success(): void
    {
        $response = $this->postJson('/api/attendances/check-in', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'notes' => 'Hadir dikantor'
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Check-in berhasil dilakukan.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'date',
                    'check_in_time',
                    'check_in_latitude',
                    'check_in_longitude',
                    'status',
                    'notes'
                ]
            ]);

        $attendance = Attendance::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(-6.2088, (float) $attendance->check_in_latitude);
        $this->assertEquals(106.8456, (float) $attendance->check_in_longitude);
        $this->assertEquals('Hadir dikantor', $attendance->notes);
    }

    /**
     * Test check-in validation.
     */
    public function test_check_in_validation(): void
    {
        $response = $this->postJson('/api/attendances/check-in', [
            'latitude' => 'invalid_latitude',
            'longitude' => 106.8456,
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    /**
     * Test duplicate check-in fails.
     */
    public function test_duplicate_check_in_fails(): void
    {
        // First check-in
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::now(),
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'present',
        ]);

        // Second check-in attempt
        $response = $this->postJson('/api/attendances/check-in', [
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in hari ini.'
            ]);
    }

    /**
     * Test check-out success.
     */
    public function test_check_out_success(): void
    {
        // Setup today's check-in
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::now()->subHours(8),
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'present',
        ]);

        $response = $this->postJson('/api/attendances/check-out', [
            'latitude' => -6.2090,
            'longitude' => 106.8458,
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Check-out berhasil dilakukan.',
            ]);

        $attendance = Attendance::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(-6.2090, (float) $attendance->check_out_latitude);
        $this->assertEquals(106.8458, (float) $attendance->check_out_longitude);
    }

    /**
     * Test check-out before check-in fails.
     */
    public function test_check_out_before_check_in_fails(): void
    {
        $response = $this->postJson('/api/attendances/check-out', [
            'latitude' => -6.2090,
            'longitude' => 106.8458,
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda harus melakukan check-in terlebih dahulu.'
            ]);
    }

    /**
     * Test today's status retrieval.
     */
    public function test_today_status(): void
    {
        // When not checked in yet
        $response1 = $this->getJson('/api/attendances/today', [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);
        $response1->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => null
            ]);

        // When checked in
        Attendance::create([
            'user_id' => $this->employee->id,
            'date' => Carbon::today()->toDateString(),
            'check_in_time' => Carbon::now(),
            'check_in_latitude' => -6.2088,
            'check_in_longitude' => 106.8456,
            'status' => 'present',
        ]);

        $response2 = $this->getJson('/api/attendances/today', [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);
        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.status', 'present');
    }

    /**
     * Test employee cannot view team attendance.
     */
    public function test_employee_cannot_view_team_attendance(): void
    {
        $responseEmployee = $this->getJson('/api/attendances/team', [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);
        $responseEmployee->assertStatus(403);
    }

    /**
     * Test manager can view team attendance.
     */
    public function test_manager_can_view_team_attendance(): void
    {
        $responseManager = $this->getJson('/api/attendances/team', [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);
        $responseManager->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
