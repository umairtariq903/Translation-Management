<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Translation;
use App\Models\Tag;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User::factory(10)->create();
        Tag::factory()->count(100000)->create()->chunk(1000, function ($tags) {
            // Insert the chunk of tags in the database
            Tag::insert($tags->toArray());
        });
        // $tags = Tag::all();
        // Translation::factory()->count(100000)->create();
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // Translation::factory()->count(100000)->create()->each(function ($translation) use ($tags) {
        //     // Randomly attach between 1 to 5 tags to each translation
        //     $translation->tags()->attach(
        //         $tags->random(rand(1, 5))->pluck('id')->toArray()
        //     );
        // });
    }
}
