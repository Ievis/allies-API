<?php

namespace Database\Seeders;

use App\Models\Course;
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
        $courses = Course::factory(7)->create();

        foreach ($courses as $course) {
            DB::table('course_user')
                ->insert([
                    'course_id' => $course->id,
                    'user_id' => User::where('role_id', 3)->inRandomOrder()->first()->id,
                    'payment_id' => 1,
                    'is_teacher' => true,
                    'is_annual' => true,
                    'expires_at' => Carbon::now(),
                    'is_main_teacher' => true,
                ]);
        }
    }
}
