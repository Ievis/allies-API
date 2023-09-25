<?php

namespace Database\Seeders;

use App\Http\Controllers\Api\V1\Telegram\Dating\UserData;
use App\Models\TelegramDatingUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TelegramDatingUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TelegramDatingUser::create([
            'username' => 'levchaba',
            'chat_id' => '1013168319',
            'name' => 'Leo',
            'subject' => 'Математика',
            'category' => 'ЕГЭ',
            'about' => 'I am Leo'
        ]);
        $users = TelegramDatingUser::factory(100)->create();
        foreach ($users as $user) {
            $user_data = new UserData($user->username, [
                'user' => $user
            ]);

            $user_data->save();
        }
    }
}
