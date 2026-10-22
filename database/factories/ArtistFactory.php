<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();
        $username = Str::slug($name, '_') . fake()->numberBetween(10, 99);

        return [
            'name' => $name,
            'username' => $username,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'bio' => fake()->sentence(8),
            'avatar_url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=' . $username,
            'cover_url' => null,
            'background_color' => fake()->randomElement(['#FFE600', '#FF5C00', '#00F0FF', '#FFFFFF', '#F7F4ED']),
            'background_id' => fake()->randomElement([null, 'dots-grid', 'stripes', 'geometric']),
            'button_shape' => fake()->randomElement(['square', 'rounded', 'pill']),
            'button_shadow' => fake()->randomElement(['none', 'hard_small', 'hard_large', 'soft']),
            'circular_icon_background' => 'auto',
            'avatar_shape' => fake()->randomElement(['circle', 'square', 'rounded']),
            'font_family' => 'Space Grotesk',
            'font_color' => '#000000',
            'button_color' => '#FFE600',
            'button_text_color' => '#000000',
            'border_style' => 'solid-3px',
            'border_color' => '#000000',
            'icon_variant' => fake()->randomElement(['default', 'black', 'white']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}
