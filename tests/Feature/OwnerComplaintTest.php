<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Kost;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerComplaintTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $tenant;
    private Kost $kost;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $this->tenant = User::create([
            'name' => 'Tenant Test',
            'email' => 'tenant@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $this->kost = Kost::create([
            'owner_id' => $this->owner->id,
            'name' => 'Kost Mawar',
            'type' => 'campur',
            'city' => 'Jakarta',
            'address' => 'Jl. Mawar No 1',
            'description' => 'Kost nyaman',
        ]);

        $this->room = Room::create([
            'kost_id' => $this->kost->id,
            'room_number' => '101',
            'room_size' => '3x4',
            'price' => 1000000,
            'deposit_amount' => 500000,
            'status' => 'occupied',
        ]);
    }

    public function test_owner_can_list_complaints_for_their_kost(): void
    {
        Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'description' => 'AC mati',
            'status' => 'sent_in',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/owner/complaints');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'AC mati');
    }

    public function test_owner_can_filter_complaints_by_status(): void
    {
        Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'description' => 'Keran bocor',
            'status' => 'sent_in',
        ]);

        Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'description' => 'Lampu padam',
            'status' => 'in_process',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/owner/complaints?status=in_process');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Lampu padam');
    }

    public function test_owner_can_show_complaint_detail(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'description' => 'Atap bocor',
            'status' => 'sent_in',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/owner/complaints/{$complaint->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $complaint->id)
            ->assertJsonPath('data.description', 'Atap bocor');
    }

    public function test_owner_can_update_complaint_status_and_feedback(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $this->room->id,
            'description' => 'Atap bocor',
            'status' => 'sent_in',
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/owner/complaints/{$complaint->id}", [
                'status' => 'in_process',
                'complaint_feedback' => 'Teknisi sedang meluncur untuk perbaikan atap.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'in_process')
            ->assertJsonPath('data.complaint_feedback', 'Teknisi sedang meluncur untuk perbaikan atap.');

        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => 'in_process',
            'complaint_feedback' => 'Teknisi sedang meluncur untuk perbaikan atap.',
        ]);
    }

    public function test_owner_cannot_access_complaint_of_another_owners_kost(): void
    {
        $otherOwner = User::create([
            'name' => 'Other Owner',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
        ]);

        $otherKost = Kost::create([
            'owner_id' => $otherOwner->id,
            'name' => 'Kost Melati',
            'type' => 'putri',
            'city' => 'Bandung',
            'address' => 'Jl. Melati No 2',
            'description' => 'Kost lain',
        ]);

        $otherRoom = Room::create([
            'kost_id' => $otherKost->id,
            'room_number' => '201',
            'room_size' => '3x3',
            'price' => 800000,
            'deposit_amount' => 400000,
            'status' => 'occupied',
        ]);

        $otherComplaint = Complaint::create([
            'user_id' => $this->tenant->id,
            'room_id' => $otherRoom->id,
            'description' => 'WiFi mati di kost melati',
            'status' => 'sent_in',
        ]);

        // Attempt index
        $indexResponse = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/owner/complaints');

        $indexResponse->assertStatus(200)
            ->assertJsonCount(0, 'data');

        // Attempt update
        $updateResponse = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/owner/complaints/{$otherComplaint->id}", [
                'status' => 'completed',
                'complaint_feedback' => 'Mencoba merespons kost orang lain',
            ]);

        $updateResponse->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Pengaduan tidak ditemukan.');
    }
}
