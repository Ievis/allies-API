<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CourseUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $user = User::where('role_id', User::TEACHER)
                ->orWhere('role_id', User::MAIN_TEACHER)
                ->inRandomOrder()
                ->first();

            DB::table('course_user')
                ->insert([
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'payment_id' => 1,
                    'is_teacher' => $user->role_id === 2 or $user->role_id === 3,
                    'is_annual' => true,
                    'expires_at' => Carbon::now(),
                    'is_main_teacher' => $user->role_id === 3,
                ]);
        }
    }
}
