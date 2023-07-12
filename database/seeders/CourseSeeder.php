<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all();
        $categories = Category::all();

        foreach ($subjects as $subject) {
            foreach ($categories as $category) {
                Course::factory(rand(2, 7))
                    ->create([
                        'subject_id' => $subject->id,
                        'category_id' => $category->id,
                    ]);
            }
        }
    }
}
