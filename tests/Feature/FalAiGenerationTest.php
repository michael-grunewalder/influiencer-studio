<?php

use App\Livewire\InfluencerWizard;
use App\Models\Team;
use App\Models\User;
use App\Services\FalAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('uploadFile returns mock URL for bearny-codes key', function () {
    $team = Team::create(['name' => 'Design Team']);
    config(['fal_api.key' => 'bearny-codes', 'services.fal.key' => 'bearny-codes']);

    Http::preventStrayRequests();

    $service = new FalAiService;
    $file = UploadedFile::fake()->image('avatar.png');

    $url = $service->uploadFile($team, $file->getRealPath());

    expect($url)->toBe('https://v3.fal.media/files/mock-image.png');
});

test('uploadFile initiates upload and PUTs content for real keys', function () {
    $team = Team::create(['name' => 'Design Team']);
    config(['fal_api.key' => 'real-looking-key', 'services.fal.key' => null]);

    Http::fake([
        'https://rest.fal.ai/storage/upload/initiate' => Http::response([
            'upload_url' => 'https://presigned-s3-upload-url.com/put-here',
            'file_url' => 'https://v3.fal.media/files/some-user/cat.png',
        ], 200),
        'https://presigned-s3-upload-url.com/put-here' => Http::response([], 200),
    ]);

    $service = new FalAiService;
    $file = UploadedFile::fake()->image('cat.png');

    $url = $service->uploadFile($team, $file->getRealPath());

    expect($url)->toBe('https://v3.fal.media/files/some-user/cat.png');

    Http::assertSent(function (Request $request) use ($file) {
        return $request->url() === 'https://rest.fal.ai/storage/upload/initiate' &&
               $request->method() === 'POST' &&
               $request['file_name'] === basename($file->getRealPath());
    });

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://presigned-s3-upload-url.com/put-here' &&
               $request->method() === 'PUT';
    });
});

test('wizard uses text-to-image ideogram v4 when no reference is provided', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    config(['fal_api.key' => 'mock-key', 'services.fal.key' => 'mock-key']);

    Http::fake([
        'https://fal.run/ideogram/v4' => Http::response([
            'images' => [
                ['url' => 'https://v3.fal.media/files/mock-generation-1.png'],
            ],
        ], 200),
    ]);

    $test = Livewire::actingAs($user)
        ->test(InfluencerWizard::class)
        ->set('name', 'Elena Sterling')
        ->set('gender', 'Female')
        ->set('age', 26)
        ->set('niches', ['Fashion'])
        ->call('nextStep') // Step 2
        ->call('nextStep') // Step 3
        ->set('backstory', 'A digital model.')
        ->call('nextStep') // Step 4
        ->set('aesthetic_vibe', 'Minimalist')
        ->call('nextStep') // Step 5
        ->call('generate')
        ->assertSet('generated_variations', function ($vars) {
            return is_array($vars) &&
                   count($vars) === 3 &&
                   str_starts_with($vars[0], 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[1], 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[2], 'https://v3.fal.media/files/');
        });

    Http::assertSent(function (Request $request) {
        if ($request->url() !== 'https://fal.run/ideogram/v4') {
            return false;
        }
        $payload = $request->data();

        return isset($payload['prompt']) &&
               isset($payload['image_size']['width']) &&
               $payload['image_size']['width'] === 768 &&
               $payload['image_size']['height'] === 1024 &&
               ! isset($payload['image_url']);
    });
});

test('wizard uses image-to-image ideogram v4 when reference image is uploaded', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    config(['fal_api.key' => 'mock-key', 'services.fal.key' => 'mock-key']);

    $faceFile = UploadedFile::fake()->image('face.png');

    Http::fake([
        'https://rest.fal.ai/storage/upload/initiate' => Http::response([
            'upload_url' => 'https://presigned-s3-upload-url.com/put-here',
            'file_url' => 'https://v3.fal.media/files/uploaded-face.png',
        ], 200),
        'https://presigned-s3-upload-url.com/put-here' => Http::response([], 200),
        'https://fal.run/ideogram/v4/image-to-image' => Http::response([
            'images' => [
                ['url' => 'https://v3.fal.media/files/mock-img2img-1.png'],
            ],
        ], 200),
    ]);

    $test = Livewire::actingAs($user)
        ->test(InfluencerWizard::class)
        ->set('name', 'Elena Sterling')
        ->set('gender', 'Female')
        ->set('age', 26)
        ->set('niches', ['Fashion'])
        ->call('nextStep') // Step 2
        ->set('face_reference', $faceFile)
        ->call('nextStep') // Step 3
        ->set('backstory', 'A digital model.')
        ->call('nextStep') // Step 4
        ->set('aesthetic_vibe', 'Minimalist')
        ->call('nextStep') // Step 5
        ->call('generate')
        ->assertSet('generated_variations', function ($vars) {
            return is_array($vars) &&
                   count($vars) === 3 &&
                   str_starts_with($vars[0], 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[1], 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[2], 'https://v3.fal.media/files/');
        });

    Http::assertSent(function (Request $request) {
        if ($request->url() !== 'https://fal.run/ideogram/v4/image-to-image') {
            return false;
        }
        $payload = $request->data();

        return isset($payload['prompt']) &&
               isset($payload['image_url']) &&
               $payload['image_url'] === 'https://v3.fal.media/files/uploaded-face.png' &&
               $payload['image_size']['width'] === 768 &&
               $payload['image_size']['height'] === 1024;
    });
});
