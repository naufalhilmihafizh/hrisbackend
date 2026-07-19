<?php

namespace Tests\Feature;

use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvertimeApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $manager;
    protected string $employeeToken;
    protected string $managerToken;

    protected function setUp(): void
    {
        parent::setUp();

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
     * Test successful overtime request.
     */
    public function test_request_overtime_success(): void
    {
        $response = $this->postJson('/api/overtimes', [
            'overtime_date' => Carbon::today()->toDateString(),
            'duration_hours' => 3.5,
            'reason' => 'Menyelesaikan debugging server',
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengajuan lembur berhasil diajukan.',
            ]);

        $dbOvertime = Overtime::where('user_id', $this->employee->id)->first();
        $this->assertNotNull($dbOvertime);
        $this->assertEquals(3.5, (float) $dbOvertime->duration_hours);
        $this->assertEquals('pending', $dbOvertime->status);
    }

    /**
     * Test duplicate overtime request fails.
     */
    public function test_duplicate_overtime_request_fails(): void
    {
        $date = Carbon::today()->toDateString();

        Overtime::create([
            'user_id' => $this->employee->id,
            'overtime_date' => $date,
            'duration_hours' => 2.0,
            'reason' => 'Lembur pertama',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/overtimes', [
            'overtime_date' => $date,
            'duration_hours' => 3.0,
            'reason' => 'Lembur kedua',
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah mengajukan lembur untuk tanggal ini (status pending/approved).'
            ]);
    }

    /**
     * Test manager approval of overtime.
     */
    public function test_manager_can_approve_overtime(): void
    {
        $overtime = Overtime::create([
            'user_id' => $this->employee->id,
            'overtime_date' => Carbon::today()->toDateString(),
            'duration_hours' => 4.0,
            'reason' => 'Deployment production',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/overtimes/{$overtime->id}/approve", [], [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('overtimes', [
            'id' => $overtime->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id,
        ]);
    }
}
