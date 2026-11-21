<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_can_be_created_listed_updated_and_deleted(): void
    {
        $payload = [
            'title' => 'Blue Period',
            'type' => 'anime',
            'episodes' => 12,
            'year' => 2021,
            'score' => 8.2,
            'genres' => ['Drama', 'Slice of Life'],
        ];

        $response = $this->postJson('/api/media', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', 'Blue Period')
            ->assertJsonPath('data.genres.0', 'Drama');
        $this->assertSame(
            ['id', 'title', 'synopsis', 'genres', 'episodes', 'year', 'type'],
            array_keys($response->json('data')),
        );

        $media = Media::firstOrFail();

        $listResponse = $this->getJson('/api/media?search=Blue&type=anime')
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id);
        $this->assertSame(['data'], array_keys($listResponse->json()));

        $this->patchJson("/api/media/{$media->id}", ['year' => 2022])
            ->assertOk()
            ->assertJsonPath('data.year', 2022)
            ->assertJsonMissingPath('data.slug')
            ->assertJsonMissingPath('data.score');

        $this->deleteJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Media berhasil dihapus.');

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_media_validation_rejects_invalid_fields(): void
    {
        $this->postJson('/api/media', [
            'title' => '',
            'type' => 'invalid',
            'score' => 11,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'type', 'score']);
    }

    public function test_media_list_is_ordered_by_id_ascending_by_default(): void
    {
        $first = Media::factory()->create(['title' => 'First Anime']);
        $second = Media::factory()->create(['title' => 'Second Anime']);
        $first->forceFill(['created_at' => now()->subDay()])->save();
        $second->forceFill(['created_at' => now()])->save();

        $response = $this->getJson('/api/media')->assertOk();

        $this->assertSame(
            [$first->id, $second->id],
            array_column($response->json('data'), 'id'),
        );
    }

    public function test_authenticated_users_can_manage_their_media_collection(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->actingAs($user)
            ->putJson("/api/collection/{$media->id}", [
                'status' => 'watching',
                'progress' => 2,
            ])
            ->assertOk();

        $this->actingAs($user)
            ->getJson('/api/collection')
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id)
            ->assertJsonMissingPath('data.0.collection');

        $this->assertDatabaseHas('media_user', [
            'user_id' => $user->id,
            'media_id' => $media->id,
            'status' => 'watching',
            'progress' => 2,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/collection/{$media->id}")
            ->assertOk();

        $this->assertDatabaseMissing('media_user', [
            'user_id' => $user->id,
            'media_id' => $media->id,
        ]);
    }

    public function test_users_can_save_multiple_media_and_share_media_with_other_users(): void
    {
        $firstUser = User::factory()->create(['name' => 'Rizal']);
        $secondUser = User::factory()->create(['name' => 'Demo User']);
        $sharedMedia = Media::factory()->create(['title' => 'Shared Anime']);
        $otherMedia = Media::factory()->create(['title' => 'Another Anime']);

        $this->actingAs($firstUser)
            ->putJson("/api/collection/{$sharedMedia->id}", ['status' => 'watching'])
            ->assertOk();
        $this->actingAs($firstUser)
            ->putJson("/api/collection/{$otherMedia->id}", ['status' => 'planned'])
            ->assertOk();
        $this->actingAs($secondUser)
            ->putJson("/api/collection/{$sharedMedia->id}", ['status' => 'completed'])
            ->assertOk();

        $this->actingAs($firstUser)
            ->getJson('/api/collection')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $sharedMedia->id])
            ->assertJsonFragment(['id' => $otherMedia->id]);

        $this->getJson("/api/media/{$sharedMedia->id}/users")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Rizal'])
            ->assertJsonFragment(['name' => 'Demo User']);

        $this->assertDatabaseCount('media_user', 3);
    }

    public function test_users_can_register_login_and_revoke_their_api_token(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'email'], 'token']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'demo@example.com',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['data' => ['id'], 'token']);

        $token = $this->postJson('/api/auth/register', [
            'name' => 'Second User',
            'email' => 'token@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->json('token');

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'token@example.com');

        $this->withToken($token)->postJson('/api/auth/logout')
            ->assertOk();

        $tokenId = explode('|', $token, 2)[0];
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/me')
            ->assertUnauthorized();
    }

    public function test_demo_seeder_creates_three_media_records_and_users(): void
    {
        $this->seed();

        $this->assertDatabaseCount('media', 3);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('media_user', 1);
        $this->assertDatabaseHas('users', [
            'id' => 1,
            'name' => 'Rizal',
            'email' => 'rizal@example.com',
        ]);
        $this->assertDatabaseHas('media', [
            'slug' => 'your-name',
            'type' => 'anime',
            'episodes' => 1,
            'year' => 2016,
            'score' => null,
            'cover_url' => null,
            'trailer_url' => null,
        ]);
    }

    public function test_users_endpoint_returns_id_name_and_email(): void
    {
        $this->seed();

        $response = $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.name', 'Rizal')
            ->assertJsonPath('data.0.email', 'rizal@example.com');

        $this->assertSame(['data'], array_keys($response->json()));
        $this->assertSame(['id', 'name', 'email'], array_keys($response->json('data.0')));
    }

    public function test_users_can_be_created_with_name_and_email(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'Rehan',
            'email' => 'rehan@example.com',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Rehan')
            ->assertJsonPath('data.email', 'rehan@example.com');

        $response->assertJsonMissingPath('data.password');
        $this->assertDatabaseHas('users', [
            'name' => 'Rehan',
            'email' => 'rehan@example.com',
        ]);
    }

    public function test_users_can_be_updated_and_deleted_without_a_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Rizal',
            'email' => 'rizal-update@example.com',
        ]);
        $media = Media::factory()->create();
        $user->media()->attach($media->id, [
            'status' => 'planned',
            'progress' => 0,
        ]);
        $user->createToken('delete-test');

        $this->patchJson("/api/users/{$user->id}", ['name' => 'Rizal Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Rizal Updated');

        $this->deleteJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Pengguna berhasil dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('media_user', [
            'user_id' => $user->id,
            'media_id' => $media->id,
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_user_collection_can_be_managed_by_user_and_media_ids(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create();

        $this->postJson("/api/users/{$user->id}/collection/{$media->id}", [
            'status' => 'watching',
            'progress' => 3,
        ])->assertOk();

        $this->getJson("/api/users/{$user->id}/collection")
            ->assertOk()
            ->assertJsonPath('data.0.id', $media->id);

        $this->assertDatabaseHas('media_user', [
            'user_id' => $user->id,
            'media_id' => $media->id,
            'status' => 'watching',
            'progress' => 3,
        ]);

        $this->deleteJson("/api/users/{$user->id}/collection/{$media->id}")
            ->assertOk();

        $this->assertDatabaseMissing('media_user', [
            'user_id' => $user->id,
            'media_id' => $media->id,
        ]);
    }
}
