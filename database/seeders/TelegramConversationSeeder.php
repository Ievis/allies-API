<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Section;
use App\Models\TelegramUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelegramConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course_id = 1;

        $user = User::where('email', 'student@mail.ru')->first() ?? User::create([
            'name' => 'admin',
            'surname' => fake()->word(),
            'description' => fake()->paragraph(2),
            'role_id' => 1,
            'email' => 'student@mail.ru',
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10)
        ]);

        $telegram_user = TelegramUser::where('user_id', $user->id)->first() ?? TelegramUser::create([
            'username' => 'username',
            'chat_id' => null,
            'user_id' => $user->id
        ]);

        DB::table('course_user')
            ->insert([
                'course_id' => $course_id,
                'user_id' => $user->id,
                'payment_id' => 1,
                'is_teacher' => false,
                'is_annual' => true,
                'expires_at' => Carbon::now(),
                'is_main_teacher' => false
            ]);

        $number_in_course = Lesson::where('is_modification', false)->count();
        $number_in_course = $number_in_course === 0
            ? 1
            : $number_in_course;

        $section = Section::create([
            'course_id' => $course_id,
            'name' => 'section'
        ]);
        Lesson::create([
            'number_in_course' => $number_in_course,
            'url' => 'url',
            'zoom_url' => null,
            'will_at' => null,
            'title' => 'title',
            'description' => 'description',
            'is_modification' => false,
            'course_id' => $course_id,
            'type_id' => 1,
            'status_id' => 2,
            'section_id' => $section->id
        ]);
    }
}
