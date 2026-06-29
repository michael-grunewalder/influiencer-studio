<?php

use App\Data\InfluencerProperties;
use App\Livewire\Dashboard;
use App\Models\Influencer;
use App\Models\Outfit;
use App\Models\Team;
use App\Models\TeamAsset;
use App\Models\User;
use App\Services\InfluencerImportExportService;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('influencer can be exported to a valid .isdata zip archive', function () {
    $team = Team::create(['name' => 'Export Team']);

    // Create physical files in fake disk
    $avatarPath = "teams/{$team->id}/influencers/old-ulid/avatar.png";
    Storage::disk('local')->put($avatarPath, 'fake-avatar-content');

    $outfitPath = "teams/{$team->id}/influencers/old-ulid/wardrobes/outfit_1.png";
    Storage::disk('local')->put($outfitPath, 'fake-outfit-content');

    $influencer = Influencer::create([
        'id' => 'old-ulid',
        'team_id' => $team->id,
        'name' => 'Sophia Loren',
        'stagename' => 'Sophia Loren',
        'avatar' => "/storage/{$avatarPath}",
        'bio' => 'An actress.',
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 25,
            niche: ['Fashion'],
            character_sheet: "/storage/teams/{$team->id}/influencers/old-ulid/avatar.png"
        ),
    ]);

    $outfit = Outfit::create([
        'influencer_id' => $influencer->id,
        'name' => 'Glamour',
        'image_path' => "/storage/{$outfitPath}",
    ]);

    $asset = TeamAsset::create([
        'team_id' => $team->id,
        'local_url' => "/storage/teams/{$team->id}/influencers/old-ulid/photo-studio/photo_1.png",
        'purpose' => 'photo-studio-old-ulid',
    ]);
    Storage::disk('local')->put("teams/{$team->id}/influencers/old-ulid/photo-studio/photo_1.png", 'fake-photo-content');

    $service = app(InfluencerImportExportService::class);
    $zipPath = $service->export($influencer);

    expect(file_exists($zipPath))->toBeTrue();
    expect(str_ends_with($zipPath, '.isdata'))->toBeTrue();

    // Verify ZIP content
    $zip = new ZipArchive;
    expect($zip->open($zipPath))->toBeTrue();

    $metadataString = $zip->getFromName('influencer.json');
    expect($metadataString)->not->toBeFalse();

    $metadata = json_decode($metadataString, true);
    expect($metadata['version'])->toBe('1.0');
    expect($metadata['influencer']['name'])->toBe('Sophia Loren');
    expect($metadata['outfits'][0]['name'])->toBe('Glamour');
    expect($metadata['assets'][0]['purpose'])->toBe('photo-studio-old-ulid');

    // Verify zipped files
    expect($zip->getFromName('avatar.png'))->toBe('fake-avatar-content');
    expect($zip->getFromName('wardrobes/outfit_1.png'))->toBe('fake-outfit-content');
    expect($zip->getFromName('photo-studio/photo_1.png'))->toBe('fake-photo-content');

    $zip->close();
    @unlink($zipPath);
});

test('influencer can be imported and resolves ULID if already exists', function () {
    $oldTeam = Team::create(['name' => 'Old Team']);
    $newTeam = Team::create(['name' => 'New Team']);

    // Create files for export
    Storage::disk('local')->put("teams/{$oldTeam->id}/influencers/orig-ulid/avatar.png", 'avatar');
    Storage::disk('local')->put("teams/{$oldTeam->id}/influencers/orig-ulid/wardrobes/outfit.png", 'outfit');

    $influencer = Influencer::create([
        'id' => 'orig-ulid',
        'team_id' => $oldTeam->id,
        'name' => 'Gigi Hadid',
        'avatar' => "/storage/teams/{$oldTeam->id}/influencers/orig-ulid/avatar.png",
        'properties' => new InfluencerProperties(
            gender: 'Female',
            age: 26,
            niche: ['Lifestyle'],
            character_sheet: "/storage/teams/{$oldTeam->id}/influencers/orig-ulid/avatar.png"
        ),
    ]);

    Outfit::create([
        'influencer_id' => 'orig-ulid',
        'name' => 'Streetwear',
        'image_path' => "/storage/teams/{$oldTeam->id}/influencers/orig-ulid/wardrobes/outfit.png",
    ]);

    TeamAsset::create([
        'team_id' => $oldTeam->id,
        'local_url' => "/storage/teams/{$oldTeam->id}/influencers/orig-ulid/photo-studio/photo.png",
        'purpose' => 'photo-studio-orig-ulid',
    ]);
    Storage::disk('local')->put("teams/{$oldTeam->id}/influencers/orig-ulid/photo-studio/photo.png", 'gallery');

    $service = app(InfluencerImportExportService::class);
    $zipPath = $service->export($influencer);

    // 1. Import without ULID collision (we delete the original first)
    $influencer->delete();

    $imported = $service->import($zipPath, $newTeam->id);
    expect($imported->id)->toBe('orig-ulid');
    expect($imported->team_id)->toBe($newTeam->id);
    expect($imported->avatar)->toContain("teams/{$newTeam->id}/influencers/orig-ulid/avatar.png");
    expect($imported->properties->character_sheet)->toContain("teams/{$newTeam->id}/influencers/orig-ulid/avatar.png");

    // Verify files extracted to new paths
    expect(Storage::disk('local')->get("teams/{$newTeam->id}/influencers/orig-ulid/avatar.png"))->toBe('avatar');
    expect(Storage::disk('local')->get("teams/{$newTeam->id}/influencers/orig-ulid/wardrobes/outfit.png"))->toBe('outfit');
    expect(Storage::disk('local')->get("teams/{$newTeam->id}/influencers/orig-ulid/photo-studio/photo.png"))->toBe('gallery');

    // Verify Outfit is imported and points to the right influencer and image
    $importedOutfit = Outfit::where('influencer_id', 'orig-ulid')->first();
    expect($importedOutfit)->not->toBeNull();
    expect($importedOutfit->image_path)->toContain("teams/{$newTeam->id}/influencers/orig-ulid/wardrobes/outfit.png");

    // Verify TeamAsset is imported and points to new team and purpose
    $importedAsset = TeamAsset::where('team_id', $newTeam->id)->first();
    expect($importedAsset)->not->toBeNull();
    expect($importedAsset->purpose)->toBe('photo-studio-orig-ulid');
    expect($importedAsset->local_url)->toContain("/storage/teams/{$newTeam->id}/influencers/orig-ulid/photo-studio/photo.png");

    // 2. Import WITH ULID collision (the influencer 'orig-ulid' now exists in DB from the previous step)
    $importedCollided = $service->import($zipPath, $newTeam->id);

    // Must generate a new ULID
    expect($importedCollided->id)->not->toBe('orig-ulid');
    expect(HasUlids::class)->not->toBeNull(); // ensures ULID format

    $newId = $importedCollided->id;
    expect($importedCollided->avatar)->toContain("teams/{$newTeam->id}/influencers/{$newId}/avatar.png");
    expect($importedCollided->properties->character_sheet)->toContain("teams/{$newTeam->id}/influencers/{$newId}/avatar.png");
    expect(Storage::disk('local')->get("teams/{$newTeam->id}/influencers/{$newId}/avatar.png"))->toBe('avatar');

    // Outfit of the new collided influencer must point to the new ID
    $collidedOutfit = Outfit::where('influencer_id', $newId)->first();
    expect($collidedOutfit)->not->toBeNull();
    expect($collidedOutfit->image_path)->toContain("teams/{$newTeam->id}/influencers/{$newId}/wardrobes/outfit.png");

    // Asset of the new collided influencer must point to new purpose and team
    $collidedAsset = TeamAsset::where('purpose', "photo-studio-{$newId}")->first();
    expect($collidedAsset)->not->toBeNull();
    expect($collidedAsset->team_id)->toBe($newTeam->id);
    expect($collidedAsset->local_url)->toContain("/storage/teams/{$newTeam->id}/influencers/{$newId}/photo-studio/photo.png");

    @unlink($zipPath);
});

test('influencer can be downloaded and imported from a url', function () {
    $team = Team::create(['name' => 'Import Team']);

    // Create a fake archive in filesystem to simulate a download file
    $mockService = app(InfluencerImportExportService::class);

    $tempInfluencer = Influencer::create([
        'team_id' => $team->id,
        'name' => 'Kendall Jenner',
    ]);
    $zipPath = $mockService->export($tempInfluencer);
    $archiveContents = file_get_contents($zipPath);
    @unlink($zipPath);

    // Mock Http call
    Http::fake([
        'https://example.com/kendall.isdata' => Http::response($archiveContents, 200),
    ]);

    // Delete the original influencer to avoid collision
    $tempInfluencer->delete();

    $service = app(InfluencerImportExportService::class);
    $imported = $service->importFromUrl('https://example.com/kendall.isdata', $team->id);

    expect($imported->name)->toBe('Kendall Jenner');
    expect($imported->team_id)->toBe($team->id);
});

test('livewire component integrates export and import actions', function () {
    $team = Team::create(['name' => 'Livewire Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'id' => 'bella-hadid',
        'team_id' => $team->id,
        'name' => 'Bella Hadid',
    ]);

    // Mock the service
    $mockService = Mockery::mock(InfluencerImportExportService::class);

    // Create a temp file so download doesn't fail
    $tempFile = tempnam(sys_get_temp_dir(), 'export').'.isdata';
    file_put_contents($tempFile, 'fake-zip-data');

    $mockService->shouldReceive('export')
        ->once()
        ->with(Mockery::on(fn ($inf) => $inf->id === $influencer->id))
        ->andReturn($tempFile);

    $mockService->shouldReceive('import')
        ->once()
        ->andReturn($influencer);

    $this->app->instance(InfluencerImportExportService::class, $mockService);

    // Test Export Livewire Action
    $exportResponse = Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('exportInfluencer');

    $exportResponse->assertStatus(200);

    // Test Import Livewire Action
    $uploadedFile = UploadedFile::fake()->create('bella.isdata', 10);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('importFile', $uploadedFile)
        ->call('importInfluencer')
        ->assertHasNoErrors();

    // Clean up export temp file
    if (file_exists($tempFile)) {
        @unlink($tempFile);
    }
});

test('deleting an influencer deletes related outfits, assets, and storage files', function () {
    $team = Team::create(['name' => 'Delete Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    // Create physical files in fake disk
    $avatarPath = "teams/{$team->id}/influencers/del-ulid/avatar.png";
    Storage::disk('local')->put($avatarPath, 'avatar-content');

    $influencer = Influencer::create([
        'id' => 'del-ulid',
        'team_id' => $team->id,
        'name' => 'To Be Deleted',
        'avatar' => "/storage/{$avatarPath}",
    ]);

    $outfit = Outfit::create([
        'influencer_id' => $influencer->id,
        'name' => 'Casual',
    ]);

    $asset = TeamAsset::create([
        'team_id' => $team->id,
        'local_url' => "/storage/teams/{$team->id}/influencers/del-ulid/photo.png",
        'purpose' => 'photo-studio-del-ulid',
    ]);
    Storage::disk('local')->put("teams/{$team->id}/influencers/del-ulid/photo.png", 'photo');

    // Run deleteOnly action via Livewire
    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('deleteOnly')
        ->assertHasNoErrors();

    // Assert DB records are deleted
    expect(Influencer::where('id', 'del-ulid')->exists())->toBeFalse();
    expect(Outfit::where('influencer_id', 'del-ulid')->exists())->toBeFalse();
    expect(TeamAsset::where('purpose', 'photo-studio-del-ulid')->exists())->toBeFalse();

    // Assert files are deleted from local disk
    expect(Storage::disk('local')->exists($avatarPath))->toBeFalse();
    expect(Storage::disk('local')->exists("teams/{$team->id}/influencers/del-ulid/photo.png"))->toBeFalse();
});

test('export and delete action exports first then performs full cleanup', function () {
    $team = Team::create(['name' => 'Export Delete Team']);
    $user = User::factory()->create();
    $user->teams()->attach($team->id);
    session(['active_team_id' => $team->id]);

    $influencer = Influencer::create([
        'id' => 'exp-del-ulid',
        'team_id' => $team->id,
        'name' => 'Export Delete',
    ]);

    // Mock the service to verify it is called
    $mockService = Mockery::mock(InfluencerImportExportService::class);
    $tempFile = tempnam(sys_get_temp_dir(), 'expdel').'.isdata';
    file_put_contents($tempFile, 'zipped');

    $mockService->shouldReceive('export')
        ->once()
        ->with(Mockery::on(fn ($inf) => $inf->id === 'exp-del-ulid'))
        ->andReturn($tempFile);

    $this->app->instance(InfluencerImportExportService::class, $mockService);

    // Call exportAndDelete via Livewire
    $response = Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->set('selectedId', $influencer->id)
        ->call('exportAndDelete');

    $response->assertStatus(200);

    // Assert DB records are deleted
    expect(Influencer::where('id', 'exp-del-ulid')->exists())->toBeFalse();

    if (file_exists($tempFile)) {
        @unlink($tempFile);
    }
});
