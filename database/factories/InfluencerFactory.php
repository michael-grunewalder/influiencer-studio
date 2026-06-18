<?php

namespace Database\Factories;

use App\Data\InfluencerProperties;
use App\Models\Influencer;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Influencer>
 */
class InfluencerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $gender = fake()->randomElement(['Female', 'Male']);
        $age = fake()->numberBetween(18, 50);
        $niche = fake()->randomElements([
            'Fashion', 'Beauty', 'Lifestyle', 'Fitness', 'Travel',
            'Food & Dining', 'Tech', 'Gaming', 'Finance',
        ], fake()->numberBetween(1, 3));

        $avatarIndex = fake()->numberBetween(1, 46);
        $ext = in_array($avatarIndex, [3, 4, 6]) ? 'jpg' : 'png';
        $avatar = "/storage/influencer/i{$avatarIndex}.{$ext}";

        $properties = new InfluencerProperties(
            gender: $gender,
            age: $age,
            niche: $niche,
            backstory: fake()->paragraph(),
            personality: fake()->numberBetween(0, 100),
            ethnicity: fake()->randomElement(['White', 'Black', 'Hispanic', 'East Asian', 'South Asian']),
            skin_tone: fake()->randomElement(['Fair', 'Light', 'Medium', 'Tan', 'Brown', 'Deep']),
            hair_color: fake()->randomElement(['Blonde', 'Brunette', 'Black', 'Auburn', 'Red']),
            hair_length: fake()->randomElement(['Short', 'Medium', 'Long']),
            hair_texture: fake()->randomElement(['Straight', 'Wavy', 'Curly']),
            eye_color: fake()->randomElement(['Blue', 'Green', 'Brown', 'Hazel']),
            build: fake()->randomElement(['Petite', 'Slim', 'Athletic', 'Average']),
            aesthetic_vibe: fake()->randomElement(['Minimalist', 'Old Money', 'Clean Girl', 'Streetwear']),
            character_sheet: '/storage/influencer/i'.fake()->numberBetween(1, 10).'.png',
            closeup: '/storage/influencer/i'.fake()->numberBetween(11, 20).'.png',
            detail_sheet: '/storage/influencer/i'.fake()->numberBetween(21, 30).'.png'
        );

        return [
            'team_id' => Team::factory(),
            'name' => $name,
            'stagename' => $name,
            'avatar' => $avatar,
            'bio' => fake()->sentence(),
            'properties' => $properties,
        ];
    }
}
