<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_create_logs_activity_with_causer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hotel = Hotel::create([
            'name' => 'Log Inn',
            'city' => 'Dubai',
            'country' => 'UAE',
            'rating' => 4,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'created',
            'causer_id' => $user->id,
            'subject_type' => Hotel::class,
            'subject_id' => $hotel->id,
        ]);
    }

    public function test_model_update_logs_dirty_attributes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hotel = Hotel::create([
            'name' => 'Before',
            'city' => 'Dubai',
            'country' => 'UAE',
            'rating' => 3,
        ]);

        $hotel->update(['rating' => 5]);

        $this->assertDatabaseHas('activity_log', [
            'event' => 'updated',
            'causer_id' => $user->id,
            'subject_id' => $hotel->id,
        ]);
    }

    public function test_soft_delete_logs_deleted_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $hotel = Hotel::create([
            'name' => 'Gone Inn',
            'city' => 'Dubai',
            'country' => 'UAE',
            'rating' => 3,
        ]);

        $hotel->delete();

        $this->assertDatabaseHas('activity_log', [
            'event' => 'deleted',
            'causer_id' => $user->id,
            'subject_id' => $hotel->id,
        ]);
    }

    public function test_location_models_support_audit_and_soft_delete(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $country = Country::create(['name' => 'Testland', 'code' => 'TL']);
        $city = City::create(['country_id' => $country->id, 'name' => 'Test City']);

        $this->assertSame($user->id, $country->created_by);
        $this->assertSame($user->id, $city->created_by);

        $city->delete();

        $this->assertSoftDeleted('cities', ['id' => $city->id]);
        $this->assertSame($user->id, City::withTrashed()->find($city->id)->deleted_by);
    }

    public function test_web_login_writes_auth_activity(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'description' => 'Logged in via web',
            'causer_id' => $user->id,
        ]);
    }
}
