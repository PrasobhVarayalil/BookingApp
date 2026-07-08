<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditAndSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function attributes(): array
    {
        return ['name' => 'Base Inn', 'city' => 'Dubai', 'country' => 'UAE', 'rating' => 5];
    }

    public function test_uuid_key_is_generated(): void
    {
        $hotel = Hotel::create($this->attributes());

        $this->assertTrue(Str::isUuid($hotel->id));
    }

    public function test_delete_is_soft_and_hidden_from_default_queries(): void
    {
        $hotel = Hotel::create($this->attributes());
        $hotel->delete();

        $this->assertSoftDeleted('hotels', ['id' => $hotel->id]);
        $this->assertNull(Hotel::find($hotel->id));
        $this->assertNotNull(Hotel::withTrashed()->find($hotel->id));
    }

    public function test_created_and_updated_by_track_the_actor(): void
    {
        $author = User::factory()->create();
        $editor = User::factory()->create();

        $this->actingAs($author);
        $hotel = Hotel::create($this->attributes());
        $this->assertSame($author->id, $hotel->created_by);
        $this->assertSame($author->id, $hotel->updated_by);

        $this->actingAs($editor);
        $hotel->update(['rating' => 4]);
        $this->assertSame($author->id, $hotel->fresh()->created_by);
        $this->assertSame($editor->id, $hotel->fresh()->updated_by);
    }

    public function test_deleted_by_is_stamped(): void
    {
        $user = User::factory()->create();
        $hotel = Hotel::create($this->attributes());

        $this->actingAs($user);
        $hotel->delete();

        $this->assertSame($user->id, Hotel::withTrashed()->find($hotel->id)->deleted_by);
    }

    public function test_audit_columns_stay_null_for_console_writes(): void
    {
        $hotel = Hotel::create($this->attributes());

        $this->assertNull($hotel->created_by);
        $this->assertNull($hotel->updated_by);
    }
}
