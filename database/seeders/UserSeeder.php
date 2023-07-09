<?php

namespace Database\Seeders;

use App\Models\TeacherDescription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(16)->create();

        foreach ($users as $user) {
            $user_id = $user->id;

            TeacherDescription::factory(6)
                ->create([
                    'user_id' => $user_id
                ]);
        }
    }
}
