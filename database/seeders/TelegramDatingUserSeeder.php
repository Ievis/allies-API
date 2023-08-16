<?php

namespace Database\Seeders;

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
            'name' => 'Leo',
            'subject' => 'Математика',
            'category' => 'ЕГЭ',
            'about' => 'I am Leo'
        ]);
        TelegramDatingUser::create([
            'username' => 'sendanother',
            'name' => 'Dima',
            'subject' => 'Математика',
            'category' => 'ЕГЭ',
            'about' => 'I am Leo'
        ]);
        TelegramDatingUser::factory(300)->create();
    }
}
