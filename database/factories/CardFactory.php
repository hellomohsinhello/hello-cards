<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'from_id' => User::factory()->create(),
            'to_id' => User::factory()->create(),
            'body' => $this->faker->text,
            'value' => $this->faker->randomElement(['Value 1', 'Value 2', 'Value 3']),
        ];
    }
}
