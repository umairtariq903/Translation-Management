<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;
use App\Models\Tag;

class TranslationSeeder extends Seeder
{
    public function run()
    {
        // Fetch all tag IDs once for efficiency
        $tagIds = Tag::pluck('id')->toArray();

        // Create 100,000 translations
        Translation::factory(100000)->create()->each(function ($translation) use ($tagIds) {
            // Randomly pick 2 tag IDs from the array
            $randomTags = collect($tagIds)->random(2);  // Picking 2 random tags

            // Attach the selected tags to the translation
            $translation->tags()->attach($randomTags);
        });
    }
}
