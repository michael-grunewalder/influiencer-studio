<?php

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Services\PromptBuilderService;

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
