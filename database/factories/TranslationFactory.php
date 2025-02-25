<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Translation;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'group' => $this->faker->word(), // Grouping translations (e.g., 'auth', 'messages')
            'key' => $this->faker->unique()->word(), // Unique key for translation
            'locale' => $this->faker->randomElement(['en', 'fr', 'es', 'de', 'it']), // Language codes
            'value' => $this->faker->sentence(), // Actual translated text
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
