<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Work;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test_User',
            'email' => 'test@example.com',
        ]);
        // User::factory(10)->create();
        Category::factory(5)->create();

        Author::factory(10)->create()
        ->each(function ($author) {
            Work::factory(rand(1, 4))->create([
                'author_id' => $author->id,
            ]);
        });

        Tag::factory(15)->create();

        Work::all()->each(function ($work){
            $tagIds = Tag::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $work->tags()->attach($tagIds);
        });

    }
}
