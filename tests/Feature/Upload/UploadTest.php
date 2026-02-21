<?php

namespace Tests\Feature\Upload;

use App\Models\User;
use App\Models\AudioFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_upload_audio_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create(
            'test.mp3',
            1000,
            'audio/mpeg'
        );

        $response = $this->actingAs($user)->postJson('/api/upload', [
            'audio_file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('audio_files', [
            'user_id' => $user->id,
            'filename' => 'test.mp3',
        ]);

        Storage::disk('public')->assertExists('audio_files/'.$file->hashName());
    }

    public function test_upload_fails_without_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/upload', []);

        $response->assertStatus(422);
    }

    public function test_upload_fails_for_invalid_mime(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create(
            'document.pdf',
            100,
            'application/pdf'
        );

        $response = $this->actingAs($user)->postJson('/api/upload', [
            'audio_file' => $file,
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_upload_duplicate_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create(
            'test.mp3',
            1000,
            'audio/mpeg'
        );

        AudioFile::factory()->create([
            'user_id' => $user->id,
            'filename' => 'test.mp3',
            'size' => $file->getSize(),
        ]);

        $response = $this->actingAs($user)->postJson('/api/upload', [
            'audio_file' => $file,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }
}
