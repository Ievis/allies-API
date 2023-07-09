<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\LessonStatus;
use App\Models\LessonType;
use App\Models\Role;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $category_names = [
            [
                'name' => 'ОГЭ',
            ],
            [
                'name' => 'ЕГЭ',
            ],
            [
                'name' => 'Олимпиады',
            ],
            [
                'name' => 'ДВИ',
            ],
            [
                'name' => '9 класс',
            ],
            [
                'name' => '11 класс',
            ],
        ];
        $roles = [
            [
                'name' => 'user'
            ],
            [
                'name' => 'teacher'
            ],
            [
                'name' => 'main_teacher'
            ],
            [
                'name' => 'admin'
            ]
        ];
        $lesson_types = [
            [
                'name' => 'public'
            ],
            [
                'name' => 'private'
            ]
        ];
        $lesson_statuses = [
            [
                'name' => 'zoom'
            ],
            [
                'name' => 'recorded'
            ]
        ];
        $subjects = [
            [
                'name' => 'Математика'
            ],
            [
                'name' => 'Химия'
            ],
            [
                'name' => 'Физика'
            ],
            [
                'name' => 'Биология'
            ],
            [
                'name' => 'Информатика'
            ],
        ];

        $this->createInstances($category_names, Category::class);
        $this->createInstances($roles, Role::class);
        $this->createInstances($lesson_types, LessonType::class);
        $this->createInstances($lesson_statuses, LessonStatus::class);
        $this->createInstances($subjects, Subject::class);
    }

    private function createInstances(array $instances, $instance_class_name)
    {
        foreach ($instances as $instance) {
            $new_instance = new $instance_class_name();
            foreach ($instance as $instance_attribute => $instance_value) {
                $new_instance->{$instance_attribute} = $instance_value;
            }
            $new_instance->save();
        }
    }
}
