<?php

namespace Database\Factories;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Button>
 */
class ButtonFactory extends Factory
{
    protected $model = Button::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(['spotify', 'instagram', 'youtube', 'tiktok', 'github', 'custom']);

        return [
            'artist_id' => Artist::factory(),
            'title' => fake()->words(3, true),
            'url' => fake()->url(),
            'platform' => $platform,
            'type' => fake()->randomElement(['standard', 'standard', 'icon_pill', 'card_featured']),
            'button_color' => fake()->optional(0.3)->hexColor(),
            'text_color' => fake()->optional(0.2)->hexColor(),
            'font_family' => null,
            'image_url' => null,
            'order_index' => fake()->numberBetween(0, 10),
            'clicks_count' => fake()->numberBetween(0, 500),
            'is_active' => true,
        ];
    }
}
