<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = Tag::factory(164)->create();

        foreach ($tags as $tag) {
            DB::table('taggables')
                ->insert([
                    'tag_id' => $tag->id,
                    'taggable_id' => Course::inRandomOrder()->first()->id,
                    'taggable_type' => Course::class
                ]);
        }
    }
}
