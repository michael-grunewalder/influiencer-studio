<?php

namespace App\Data;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class InfluencerProperties implements Castable
{
    public function __construct(
        public ?string $gender = null,
        public ?int $age = null,
        public array $niche = [],
        public ?string $backstory = null,
        public int $personality = 50,
        public ?string $face_reference = null,
        public ?string $style_reference = null,
        public ?string $ethnicity = null,
        public ?string $skin_tone = null,
        public ?string $hair_color = null,
        public ?string $hair_length = null,
        public ?string $hair_texture = null,
        public ?string $eye_color = null,
        public ?string $build = null,
        public ?string $custom_description = null,
        public ?string $aesthetic_vibe = null,
        public ?string $character_sheet = null,
        public ?string $closeup = null,
        public ?string $detail_sheet = null,
        public ?string $location = null,
        public ?string $target_audience = null,
        public ?string $physical_description = null,
    ) {}

    /**
     * Get the caster class to use when casting from / to this castable type.
     *
     * @param  array<string, mixed>  $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes
        {
            public function get($model, string $key, $value, array $attributes): ?InfluencerProperties
            {
                if (! $value) {
                    return new InfluencerProperties;
                }

                $data = json_decode($value, true);
                if (! is_array($data)) {
                    return new InfluencerProperties;
                }

                return new InfluencerProperties(
                    gender: $data['gender'] ?? null,
                    age: isset($data['age']) ? (int) $data['age'] : null,
                    niche: $data['niche'] ?? [],
                    backstory: $data['backstory'] ?? null,
                    personality: isset($data['personality']) ? (int) $data['personality'] : 50,
                    face_reference: $data['face_reference'] ?? null,
                    style_reference: $data['style_reference'] ?? null,
                    ethnicity: $data['ethnicity'] ?? null,
                    skin_tone: $data['skin_tone'] ?? null,
                    hair_color: $data['hair_color'] ?? null,
                    hair_length: $data['hair_length'] ?? null,
                    hair_texture: $data['hair_texture'] ?? null,
                    eye_color: $data['eye_color'] ?? null,
                    build: $data['build'] ?? null,
                    custom_description: $data['custom_description'] ?? null,
                    aesthetic_vibe: $data['aesthetic_vibe'] ?? null,
                    character_sheet: $data['character_sheet'] ?? null,
                    closeup: $data['closeup'] ?? null,
                    detail_sheet: $data['detail_sheet'] ?? null,
                    location: $data['location'] ?? null,
                    target_audience: $data['target_audience'] ?? null,
                    physical_description: $data['physical_description'] ?? null,
                );
            }

            public function set($model, string $key, $value, array $attributes): ?string
            {
                if (! $value instanceof InfluencerProperties) {
                    return null;
                }

                return json_encode([
                    'gender' => $value->gender,
                    'age' => $value->age,
                    'niche' => $value->niche,
                    'backstory' => $value->backstory,
                    'personality' => $value->personality,
                    'face_reference' => $value->face_reference,
                    'style_reference' => $value->style_reference,
                    'ethnicity' => $value->ethnicity,
                    'skin_tone' => $value->skin_tone,
                    'hair_color' => $value->hair_color,
                    'hair_length' => $value->hair_length,
                    'hair_texture' => $value->hair_texture,
                    'eye_color' => $value->eye_color,
                    'build' => $value->build,
                    'custom_description' => $value->custom_description,
                    'aesthetic_vibe' => $value->aesthetic_vibe,
                    'character_sheet' => $value->character_sheet,
                    'closeup' => $value->closeup,
                    'detail_sheet' => $value->detail_sheet,
                    'location' => $value->location,
                    'target_audience' => $value->target_audience,
                    'physical_description' => $value->physical_description,
                ]);
            }
        };
    }

    /**
     * Convert properties to array.
     */
    public function toArray(): array
    {
        return [
            'gender' => $this->gender,
            'age' => $this->age,
            'niche' => $this->niche,
            'backstory' => $this->backstory,
            'personality' => $this->personality,
            'face_reference' => $this->face_reference,
            'style_reference' => $this->style_reference,
            'ethnicity' => $this->ethnicity,
            'skin_tone' => $this->skin_tone,
            'hair_color' => $this->hair_color,
            'hair_length' => $this->hair_length,
            'hair_texture' => $this->hair_texture,
            'eye_color' => $this->eye_color,
            'build' => $this->build,
            'custom_description' => $this->custom_description,
            'aesthetic_vibe' => $this->aesthetic_vibe,
            'character_sheet' => $this->character_sheet,
            'closeup' => $this->closeup,
            'detail_sheet' => $this->detail_sheet,
            'location' => $this->location,
            'target_audience' => $this->target_audience,
            'physical_description' => $this->physical_description,
        ];
    }
}
