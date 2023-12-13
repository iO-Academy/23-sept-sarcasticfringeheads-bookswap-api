<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(200),
            'author' => $this->faker->text(20),
            'user_email' => 'elonmusk@tesla.com',
            'user_name' => 'Elon Musk',
            'blurb' => $this->faker->text(200),
            'year' => $this->faker->year(),
            'image' => $this->faker->imageUrl(),
            'genre_id' => Genre::factory(),
            'created_at' => $this->faker->dateTime(),
            'updated_at' => $this->faker->dateTime(),
            'claimed' => 0
        ];
    }
}
