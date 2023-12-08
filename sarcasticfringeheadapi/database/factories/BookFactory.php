<?php

namespace Database\Factories;

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
            'author'=> $this->faker->text(100),
            'image'=> $this->faker->imageUrl(),
            'genre_id'=> rand(0,3),
            'created_at'=> $this->faker->dateTime(),
            'updated_at'=> $this->faker->dateTime(),
            'claimed'=>rand(0,1)
        ];
    }
}
