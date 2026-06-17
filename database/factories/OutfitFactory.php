<?php

namespace Database\Factories;

use App\Models\Influencer;
use App\Models\Outfit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outfit>
 */
class OutfitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'influencer_id' => Influencer::factory(),
            'name' => fake()->words(2, true),
            'top' => fake()->word().' shirt',
            'bottom' => fake()->word().' pants',
            'hairstyle' => fake()->word().' hair',
            'full_look_description' => fake()->sentence(),
            'image_path' => 'https://picsum.photos/720/1280',
        ];
    }
}
