<?php

namespace Tests\Feature;

use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $manager;
    protected User $anotherEmployee;
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

        $this->anotherEmployee = User::create([
            'name' => 'Jane Employee',
            'email' => 'jane@hris.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'is_active' => true,
        ]);
    }

    /**
     * Test successful leave request.
     */
    public function test_request_leave_success(): void
    {
        $response = $this->postJson('/api/leaves', [
            'leave_type' => 'annual',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->addDays(2)->toDateString(),
            'reason' => 'Liburan keluarga',
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Pengajuan cuti berhasil diajukan.',
            ]);

        $this->assertDatabaseHas('leaves', [
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'reason' => 'Liburan keluarga',
            'status' => 'pending',
        ]);
    }

    /**
     * Test overlapping leave request validation.
     */
    public function test_overlapping_leave_request_fails(): void
    {
        $startDate = Carbon::tomorrow()->toDateString();
        $endDate = Carbon::tomorrow()->addDays(2)->toDateString();

        // Create an initial approved leave
        Leave::create([
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'Cuti awal',
            'status' => 'approved',
        ]);

        // Attempt overlapping leave request
        $response = $this->postJson('/api/leaves', [
            'leave_type' => 'sick',
            'start_date' => Carbon::tomorrow()->addDay()->toDateString(), // overlaps
            'end_date' => Carbon::tomorrow()->addDays(3)->toDateString(),
            'reason' => 'Sakit demam',
        ], [
            'Authorization' => 'Bearer ' . $this->employeeToken,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah mengajukan cuti pada tanggal tersebut (status pending/approved).'
            ]);
    }

    /**
     * Test manager leaves pending list retrieval.
     */
    public function test_manager_can_view_pending_leaves(): void
    {
        Leave::create([
            'user_id' => $this->employee->id,
            'leave_type' => 'sick',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'reason' => 'Sakit gigi',
            'status' => 'pending',
        ]);

        Leave::create([
            'user_id' => $this->anotherEmployee->id, // not managed by this manager
            'leave_type' => 'sick',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'reason' => 'Sakit flu',
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/leaves/pending', [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // only the subordinate's leave is shown
            ->assertJsonPath('data.0.reason', 'Sakit gigi');
    }

    /**
     * Test manager approval success.
     */
    public function test_manager_can_approve_leave(): void
    {
        $leave = Leave::create([
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'reason' => 'Urusan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/leaves/{$leave->id}/approve", [], [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.approved_by', $this->manager->id);

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'approved',
            'approved_by' => $this->manager->id,
        ]);
    }

    /**
     * Test manager rejection success.
     */
    public function test_manager_can_reject_leave(): void
    {
        $leave = Leave::create([
            'user_id' => $this->employee->id,
            'leave_type' => 'annual',
            'start_date' => Carbon::tomorrow()->toDateString(),
            'end_date' => Carbon::tomorrow()->toDateString(),
            'reason' => 'Urusan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/leaves/{$leave->id}/reject", [
            'rejection_reason' => 'Tenaga kerja sedang terbatas.',
        ], [
            'Authorization' => 'Bearer ' . $this->managerToken,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Tenaga kerja sedang terbatas.');
    }
}
