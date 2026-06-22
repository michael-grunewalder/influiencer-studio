<?php

use App\Models\Team;
use App\Models\User;
use App\Services\FalAiService;
use Illuminate\Support\Facades\Http;

test('credits can be transferred from user to team', function () {
    $user = User::factory()->create(['credits' => 50.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);

    $user->transferCreditsToTeam($team, 15.50);

    $user->refresh();
    $team->refresh();

    expect((float) $user->credits)->toBe(34.50);
    expect((float) $team->credits)->toBe(25.50);
});

test('credit transfer throws exception for invalid amount', function () {
    $user = User::factory()->create(['credits' => 50.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);

    expect(fn () => $user->transferCreditsToTeam($team, -5.00))
        ->toThrow(InvalidArgumentException::class, 'Transfer amount must be greater than zero.');
});

test('credit transfer throws exception for insufficient balance', function () {
    $user = User::factory()->create(['credits' => 5.00]);
    $team = Team::create(['name' => 'Design Team', 'credits' => 10.00]);

    expect(fn () => $user->transferCreditsToTeam($team, 10.00))
        ->toThrow(Exception::class, 'Insufficient personal credits.');
});

test('team credit check helpers work correctly', function () {
    $team = Team::create(['name' => 'Design Team', 'credits' => 1.00]);

    expect($team->hasCreditsFor(2))->toBeTrue(); // 2 * 0.35 = 0.70 <= 1.00
    expect($team->hasCreditsFor(3))->toBeFalse(); // 3 * 0.35 = 1.05 > 1.00

    $team->chargeForImages(2);
    $team->refresh();

    expect((float) $team->credits)->toBe(0.30);
});

test('falai service uses team api key directly without charging credits', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'fal_api_key' => 'team-specific-api-key',
        'credits' => 0.00, // zero credits
    ]);

    Http::fake([
        'https://fal.run/*' => Http::response([
            'images' => [['url' => 'https://v3.fal.media/mock.png']],
        ], 200),
    ]);

    $service = new FalAiService;
    $result = $service->generate($team, 'Test prompt');

    expect($result)->toHaveKey('images');
    expect($team->refresh()->credits)->toBe('0.00'); // credits unchanged
});

test('falai service falls back to global key and charges credits', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 1.00,
    ]);

    config(['services.fal.key' => 'global-api-key', 'fal_api.key' => 'global-api-key']);

    Http::fake([
        'https://fal.run/*' => Http::response([
            'images' => [['url' => 'https://v3.fal.media/mock.png']],
        ], 200),
    ]);

    $service = new FalAiService;
    $result = $service->generate($team, 'Test prompt');

    expect($result)->toHaveKey('images');
    // charges 1 image * $0.35 = $0.35. New balance should be $0.65
    expect((float) $team->refresh()->credits)->toBe(0.65);
});

test('falai service throws exception if team lacks credits and no key', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 0.20, // insufficient for 1 image ($0.35)
    ]);

    config(['services.fal.key' => 'global-api-key', 'fal_api.key' => 'global-api-key']);

    $service = new FalAiService;

    expect(fn () => $service->generate($team, 'Test prompt'))
        ->toThrow(Exception::class, 'Insufficient team credits.');
});

test('falai service throws exception if no API key is configured', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 10.00,
    ]);

    config(['services.fal.key' => null, 'fal_api.key' => null]);
    putenv('FAL_API_KEY='); // Clear env fallback

    $service = new FalAiService;

    expect(fn () => $service->generate($team, 'Test prompt'))
        ->toThrow(Exception::class, 'No API key configured for the team or globally.');
});

test('falai service bypasses API, returns watermarked demo image, and deducts credits if key is bearny-codes in local env', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 10.00,
    ]);

    config(['services.fal.key' => 'bearny-codes', 'fal_api.key' => 'bearny-codes']);

    Http::preventStrayRequests();

    $service = new FalAiService;
    $result = $service->generate($team, 'Test prompt');

    expect($result)->toHaveKey('images');
    $url = $result['images'][0]['url'];
    expect($url)->toStartWith('/storage/references/demo_');
    expect($url)->toEndWith('.png');

    $filePath = public_path($url);
    expect(file_exists($filePath))->toBeTrue();

    @unlink($filePath);

    // Assert credits were deducted: 10.00 - 0.35 = 9.65
    expect((float) $team->refresh()->credits)->toBe(9.65);
});

test('falai service does not bypass API if key is bearny-codes in non-local env', function () {
    $team = Team::create([
        'name' => 'Design Team',
        'credits' => 10.00,
    ]);

    config(['services.fal.key' => 'bearny-codes', 'fal_api.key' => 'bearny-codes']);

    // Change environment to production
    $originalEnv = app()->environment();
    app()->detectEnvironment(fn () => 'production');

    Http::fake([
        'https://fal.run/*' => Http::response([
            'images' => [['url' => 'https://v3.fal.media/mock-production.png']],
        ], 200),
    ]);

    $service = new FalAiService;

    try {
        $result = $service->generate($team, 'Test prompt');

        expect($result)->toHaveKey('images');
        expect($result['images'][0]['url'])->toBe('https://v3.fal.media/mock-production.png');

        // Assert credits were deducted
        expect((float) $team->refresh()->credits)->toBe(9.65);
    } finally {
        // Restore environment
        app()->detectEnvironment(fn () => $originalEnv);
    }
});
