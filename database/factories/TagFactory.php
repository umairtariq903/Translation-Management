<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tag;

class TagFactory extends Factory
{
    protected $model = Tag::class;



    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(5, true), // Generates a unique tag name
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
