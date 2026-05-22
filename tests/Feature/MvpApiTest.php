<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class MvpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_service(): void
    {
        $business = Business::create(['name' => 'Acme']);
        $owner = User::factory()->create(['business_id' => $business->id, 'role' => 'owner']);

        $response = $this->actingAs($owner)->postJson('/api/services', [
            'name' => 'Haircut',
            'duration_minutes' => 30,
            'price_cents' => 2500,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('services', ['name' => 'Haircut', 'business_id' => $business->id]);
    }

    public function test_public_booking_returns_payment_url(): void
    {
        $business = Business::create(['name' => 'Acme']);
        $service = Service::create(['business_id' => $business->id, 'name' => 'Nails', 'duration_minutes' => 45, 'price_cents' => 3000, 'is_active' => true]);

        $response = $this->postJson('/api/public/bookings', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'customer_name' => 'Jane',
            'customer_email' => 'jane@example.com',
            'starts_at' => now()->addDay()->toISOString(),
        ]);

        $response->assertCreated()->assertJsonStructure(['appointment_id', 'payment_url']);
    }
}
