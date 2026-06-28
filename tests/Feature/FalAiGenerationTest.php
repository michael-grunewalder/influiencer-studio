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
        'https://queue.fal.run/ideogram/v4/requests/*/status' => Http::response(['status' => 'COMPLETED'], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_1' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-generation-1.png']]], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_2' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-generation-2.png']]], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_3' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-generation-3.png']]], 200),
        'https://queue.fal.run/ideogram/v4' => Http::sequence()
            ->push(['request_id' => 'mock_1'])
            ->push(['request_id' => 'mock_2'])
            ->push(['request_id' => 'mock_3']),
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
        ->assertSet('is_generating', true)
        ->call('checkWizardGenerationProgress')
        ->assertSet('is_generating', false)
        ->assertSet('generated_variations', function ($vars) {
            return is_array($vars) &&
                   count($vars) === 3 &&
                   str_starts_with($vars[0]['url'] ?? '', 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[1]['url'] ?? '', 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[2]['url'] ?? '', 'https://v3.fal.media/files/');
        });

    Http::assertSent(function (Request $request) {
        if ($request->url() !== 'https://queue.fal.run/ideogram/v4') {
            return false;
        }
        $payload = $request->data();

        return isset($payload['prompt']) &&
               $payload['image_size'] === 'portrait_4_3' &&
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
        'https://queue.fal.run/ideogram/v4/requests/*/status' => Http::response(['status' => 'COMPLETED'], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_img2img_1' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-img2img-1.png']]], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_img2img_2' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-img2img-2.png']]], 200),
        'https://queue.fal.run/ideogram/v4/requests/mock_img2img_3' => Http::response(['images' => [['url' => 'https://v3.fal.media/files/mock-img2img-3.png']]], 200),
        'https://queue.fal.run/ideogram/v4/image-to-image' => Http::sequence()
            ->push(['request_id' => 'mock_img2img_1'])
            ->push(['request_id' => 'mock_img2img_2'])
            ->push(['request_id' => 'mock_img2img_3']),
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
        ->assertSet('is_generating', true)
        ->call('checkWizardGenerationProgress')
        ->assertSet('is_generating', false)
        ->assertSet('generated_variations', function ($vars) {
            return is_array($vars) &&
                   count($vars) === 3 &&
                   str_starts_with($vars[0]['url'] ?? '', 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[1]['url'] ?? '', 'https://v3.fal.media/files/') &&
                   str_starts_with($vars[2]['url'] ?? '', 'https://v3.fal.media/files/');
        });

    Http::assertSent(function (Request $request) {
        if ($request->url() !== 'https://queue.fal.run/ideogram/v4/image-to-image') {
            return false;
        }
        $payload = $request->data();

        return isset($payload['prompt']) &&
               isset($payload['image_url']) &&
               $payload['image_url'] === 'https://v3.fal.media/files/uploaded-face.png' &&
               $payload['image_size'] === 'portrait_4_3';
    });
});

test('wizard handles partial generation failure and retry successfully', function () {
    $user = User::factory()->create();
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);
    $user->teams()->attach($team);

    config(['fal_api.key' => 'mock-key', 'services.fal.key' => 'mock-key']);

    // Mock first call: 2 success, 1 failure (request 3)
    Http::fake(function (Request $request) {
        if ($request->url() === 'https://queue.fal.run/ideogram/v4') {
            static $postCount = 0;
            $postCount++;

            return Http::response(['request_id' => 'mock_'.$postCount], 200);
        }
        if (str_contains($request->url(), '/requests/') && str_contains($request->url(), '/status')) {
            return Http::response(['status' => 'COMPLETED'], 200);
        }
        if (str_ends_with($request->url(), '/requests/mock_1')) {
            return Http::response(['images' => [['url' => 'https://v3.fal.media/files/success-1.png']]], 200);
        }
        if (str_ends_with($request->url(), '/requests/mock_2')) {
            return Http::response(['images' => [['url' => 'https://v3.fal.media/files/success-2.png']]], 200);
        }
        if (str_ends_with($request->url(), '/requests/mock_3')) {
            return Http::response(['detail' => 'Safety filter triggered'], 400);
        }
    });

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
        ->assertSet('is_generating', true)
        ->call('checkWizardGenerationProgress')
        ->assertSet('is_generating', false);

    // Assert first two are success, third is failed
    $test->assertSet('generated_variations', function ($vars) {
        return is_array($vars) &&
               count($vars) === 3 &&
               $vars[0]['status'] === 'success' &&
               $vars[0]['url'] === 'https://v3.fal.media/files/success-1.png' &&
               $vars[1]['status'] === 'success' &&
               $vars[1]['url'] === 'https://v3.fal.media/files/success-2.png' &&
               $vars[2]['status'] === 'failed' &&
               str_contains($vars[2]['error'], 'Safety filter triggered');
    });

    // Check that credits were charged only for 2 successful images (2 * $0.35 = $0.70 deducted from $10.00 = $9.30)
    $team->refresh();
    expect((float) $team->credits)->toBe(9.30);

    // Call retry for the failed variation (index 2)
    Http::fake(function (Request $request) {
        if ($request->url() === 'https://queue.fal.run/ideogram/v4') {
            return Http::response(['request_id' => 'mock_4'], 200);
        }
        if (str_contains($request->url(), '/requests/mock_4/status')) {
            return Http::response(['status' => 'COMPLETED'], 200);
        }
        if (str_ends_with($request->url(), '/requests/mock_4')) {
            return Http::response(['images' => [['url' => 'https://v3.fal.media/files/success-4.png']]], 200);
        }
    });

    $test->call('retryGeneration', 2)
        ->assertSet('is_generating', true)
        ->call('checkWizardGenerationProgress')
        ->assertSet('is_generating', false);

    $test->assertSet('generated_variations', function ($vars) {
        return is_array($vars) &&
               count($vars) === 3 &&
               $vars[0]['status'] === 'success' &&
               $vars[1]['status'] === 'success' &&
               $vars[2]['status'] === 'success' &&
               $vars[2]['url'] === 'https://v3.fal.media/files/success-4.png';
    });

    // Check credits charged again (1 successful retry -> 1 * $0.35 = $0.35 deducted from $9.30 = $8.95)
    $team->refresh();
    expect((float) $team->credits)->toBe(8.95);
});
