<?php

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Services\PromptBuilderService;
use Illuminate\Support\Facades\Log;

test('prompt builder builds physical description string correctly', function () {
    $data = [
        'ethnicity' => 'East Asian',
        'skin_tone' => 'Light',
        'hair_color' => 'Black',
        'hair_length' => 'Medium',
        'hair_texture' => 'Straight',
        'eye_color' => 'Brown',
        'build' => 'Average',
        'custom_description' => 'dimples on left cheek',
    ];

    $desc = PromptBuilderService::buildPhysicalDescString($data);

    expect($desc)->toBe('east asian, medium straight black hair, brown eyes, light skin tone, average build, dimples on left cheek');
});

test('prompt builder extracts backstory context correctly', function () {
    // Yoga teacher should match the yoga archetype in config/prompts.php
    $context = PromptBuilderService::getBackstoryContext('athletic build', 'I work as a yoga instructor in SoHo.');

    expect($context['sceneNiche'])->toBe('fitness');
    expect($context['buildHint'])->toBe('lean, flexible, long-limbed');
    expect($context['lockedScene'])->toContain('yoga studio');
    expect($context['tags'])->toContain('sport');
    expect($context['tags'])->toContain('natural');
});

test('prompt builder generates three variation prompts', function () {
    $data = [
        'gender' => 'female',
        'age' => 25,
        'niches' => ['Fashion'],
        'backstory' => 'A stylish lifestyle blogger.',
        'personality' => 50,
        'ethnicity' => 'White',
        'skin_tone' => 'Fair',
        'hair_color' => 'Blonde',
        'hair_length' => 'Long',
        'hair_texture' => 'Straight',
        'eye_color' => 'Blue',
        'build' => 'Petite',
    ];

    $prompts = PromptBuilderService::buildThreeVariationPrompts($data);

    expect($prompts)->toBeArray()->toHaveCount(3);
    expect($prompts[0])->toContain('Photograph style: iPhone 16 Pro snapshot.');
    expect($prompts[0])->toContain('Subject: female, 25 year old');
    expect($prompts[1])->toContain('Pose:');
    expect($prompts[2])->toContain('Wardrobe & details:');
});

test('prompt builder generates influencer character sheet prompt', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel and fashion model.',
        personality: 50,
        ethnicity: 'White',
        skin_tone: 'Fair',
        hair_color: 'Blonde',
        hair_length: 'Long',
        hair_texture: 'Straight',
        eye_color: 'Blue',
        build: 'Petite',
        custom_description: 'freckles',
        aesthetic_vibe: 'Coastal'
    );

    $influencer = new Influencer([
        'name' => 'Emma',
        'backstory' => 'Travel and fashion model.',
        'properties' => $properties,
    ]);

    $prompt = PromptBuilderService::buildInfluencerSheetPrompt($influencer);

    expect($prompt)->toContain('Professional full-body character turnaround sheet.');
    expect($prompt)->toContain('Panel 1 — "FRONT VIEW"');
    expect($prompt)->toContain('Panel 2 — "SIDE VIEW"');
    expect($prompt)->toContain('Panel 3 — "BACK VIEW"');
    expect($prompt)->toContain('Panel 4 — "THREE-QUARTER VIEW"');
    expect($prompt)->toContain('fair skin tone');
    expect($prompt)->toContain('freckles');
    expect($prompt)->toContain('Outfit: Coastal');
    expect($prompt)->not->toContain('Let this inform');
});

test('prompt builder generates influencer closeup prompt', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel and fashion model.',
        personality: 50,
        ethnicity: 'White',
        skin_tone: 'Fair',
        hair_color: 'Blonde',
        hair_length: 'Long',
        hair_texture: 'Straight',
        eye_color: 'Blue',
        build: 'Petite',
        custom_description: 'freckles',
        aesthetic_vibe: 'Coastal'
    );

    $influencer = new Influencer([
        'name' => 'Emma',
        'backstory' => 'Travel and fashion model.',
        'properties' => $properties,
    ]);

    $prompt = PromptBuilderService::buildCloseUpPrompt($influencer);

    expect($prompt)->toContain('Professional studio headshot.');
    expect($prompt)->toContain('The subject: white, long straight blonde hair, blue eyes, fair skin tone, petite build, freckles.');
    expect($prompt)->toContain('Shot on Phase One IQ4 150MP');
});

test('prompt builder generates influencer feature sheet prompt', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel and fashion model.',
        personality: 50,
        ethnicity: 'White',
        skin_tone: 'Fair',
        hair_color: 'Blonde',
        hair_length: 'Long',
        hair_texture: 'Straight',
        eye_color: 'Blue',
        build: 'Petite',
        custom_description: 'freckles',
        aesthetic_vibe: 'Coastal'
    );

    $influencer = new Influencer([
        'name' => 'Emma',
        'backstory' => 'Travel and fashion model.',
        'properties' => $properties,
    ]);

    $prompt = PromptBuilderService::buildFeatureSheetPrompt($influencer);

    expect($prompt)->toContain('Beauty model feature reference sheet.');
    expect($prompt)->toContain('The subject: white, long straight blonde hair, blue eyes, fair skin tone, petite build, freckles.');
    expect($prompt)->toContain('labelled "EYE"');
    expect($prompt)->toContain('labelled "BROW"');
    expect($prompt)->toContain('labelled "LIP"');
    expect($prompt)->toContain('labelled "SKIN TEXTURE"');
});

test('logPrompt writes to debug log if app debug is true and log level is debug', function () {
    config([
        'app.debug' => true,
        'logging.level' => 'debug',
    ]);

    Log::shouldReceive('debug')
        ->once()
        ->with("[Test Label] Prompt:\nHello World");

    PromptBuilderService::logPrompt('Hello World', null, 'Test Label');
});

test('logPrompt writes both base and enhanced prompts if base prompt is provided', function () {
    config([
        'app.debug' => true,
        'logging.level' => 'debug',
    ]);

    Log::shouldReceive('debug')
        ->once()
        ->with("[Test Label] Base Prompt:\nBase Hello");

    Log::shouldReceive('debug')
        ->once()
        ->with("[Test Label] Enhanced Prompt:\nEnhanced Hello");

    PromptBuilderService::logPrompt('Enhanced Hello', 'Base Hello', 'Test Label');
});

test('logPrompt does not write to debug log if app debug is false', function () {
    config([
        'app.debug' => false,
        'logging.level' => 'debug',
    ]);

    Log::shouldReceive('debug')->never();

    PromptBuilderService::logPrompt('Hello World', null, 'Test Label');
});

test('logPrompt does not write to debug log if log level is not debug', function () {
    config([
        'app.debug' => true,
        'logging.level' => 'info',
    ]);

    Log::shouldReceive('debug')->never();

    PromptBuilderService::logPrompt('Hello World', null, 'Test Label');
});

test('prompt builder generates photo studio prompt correctly', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel and fashion model.',
        personality: 50,
        ethnicity: 'White',
        skin_tone: 'Fair',
        hair_color: 'Blonde',
        hair_length: 'Long',
        hair_texture: 'Straight',
        eye_color: 'Blue',
        build: 'Petite',
        custom_description: 'freckles',
        aesthetic_vibe: 'Coastal'
    );

    $influencer = new Influencer([
        'name' => 'Emma',
        'backstory' => 'Travel and fashion model.',
        'properties' => $properties,
    ]);

    $args = [
        'influencer' => $influencer,
        'location' => 'coffee-shop',
        'timeOfDay' => 'golden-hour',
        'pose' => 'front',
        'vibe' => 'editorial',
        'stance' => 'standing',
        'aspectRatio' => '9:16',
        'expression' => 'smiling',
        'gaze' => 'at-camera',
        'poseTag' => '@image1',
        'faceTag' => '@image2',
        'wardrobeTag' => '@image3',
    ];

    $prompt = PromptBuilderService::buildPhotoStudioPrompt($args);

    expect($prompt)->toContain('Modify the base image @image1 to show a Editorial photo of the subject from @image2');
    expect($prompt)->toContain('wearing the complete outfit from @image3');
    expect($prompt)->toContain('Subject is standing upright on both feet');
    expect($prompt)->toContain('Facing the camera directly, confident and composed.');
    expect($prompt)->toContain('Expression: a genuine soft smile');
    expect($prompt)->toContain('The location is a coffee shop interior.');
    expect($prompt)->toContain('golden hour');
});

test('prompt builder generates photo studio prompt with custom location and pose overrides', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel and fashion model.',
        personality: 50,
        ethnicity: 'White',
        skin_tone: 'Fair',
        hair_color: 'Blonde',
        hair_length: 'Long',
        hair_texture: 'Straight',
        eye_color: 'Blue',
        build: 'Petite',
        custom_description: 'freckles',
        aesthetic_vibe: 'Coastal'
    );

    $influencer = new Influencer([
        'name' => 'Emma',
        'backstory' => 'Travel and fashion model.',
        'properties' => $properties,
    ]);

    $args = [
        'influencer' => $influencer,
        'location' => null,
        'locationText' => 'a high-tech cyber street in Tokyo',
        'timeOfDay' => 'night',
        'pose' => null,
        'poseText' => 'leaning against a neon sign post, arms crossed',
        'vibe' => 'street',
        'stance' => 'standing',
        'aspectRatio' => '9:16',
        'expression' => 'natural',
        'gaze' => 'looking-away',
        'faceTag' => '@image1',
        'wardrobeTag' => '@image2',
    ];

    $prompt = PromptBuilderService::buildPhotoStudioPrompt($args);

    expect($prompt)->toContain('Street style photo of the subject from @image1');
    expect($prompt)->toContain('wearing the complete outfit from @image2');
    expect($prompt)->not->toContain('Subject is standing upright on both feet');
    expect($prompt)->not->toContain('Subject is clearly seated');
    expect($prompt)->toContain('leaning against a neon sign post, arms crossed');
    expect($prompt)->toContain('The location is a high-tech cyber street in Tokyo.');
    expect($prompt)->not->toContain('Modify the base image @image1');
    expect($prompt)->not->toContain('9:16, chest-up framing');
});

test('prompt builder omits stance prefix and default framing when custom pose is provided', function () {
    $properties = new InfluencerProperties(
        gender: 'female',
        age: 25,
        niche: ['Fashion'],
        backstory: 'Travel model.',
        personality: 50,
        ethnicity: 'East Asian',
        skin_tone: 'Light',
        hair_color: 'Brunette',
        hair_length: 'Medium',
        hair_texture: 'Wavy',
        eye_color: 'Dark',
        build: 'Petite',
    );

    $influencer = new Influencer([
        'name' => 'Elena',
        'properties' => $properties,
    ]);

    $args = [
        'influencer' => $influencer,
        'location' => 'park',
        'timeOfDay' => 'afternoon',
        'pose' => null,
        'poseText' => 'subject sitting on the ground, legs gently pulled towards the body, full body shot, feet visible',
        'vibe' => 'candid',
        'stance' => 'standing', // even if stance is set to standing, it should be ignored/omitted!
        'aspectRatio' => '16:9',
        'expression' => 'laughing',
        'gaze' => 'at-camera',
        'faceTag' => '@image1',
    ];

    $prompt = PromptBuilderService::buildPhotoStudioPrompt($args);

    // Stance prefixes must be completely omitted
    expect($prompt)->not->toContain('Subject is standing upright');
    expect($prompt)->not->toContain('Subject is clearly seated');

    // Default framing suffix must be omitted
    expect($prompt)->not->toContain('16:9, waist-up');
    expect($prompt)->not->toContain('9:16, chest-up');

    // Custom pose text and other elements must be retained correctly
    expect($prompt)->toContain('subject sitting on the ground, legs gently pulled towards the body, full body shot, feet visible');
});
