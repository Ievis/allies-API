<?php

namespace App\Http\Resources\V1\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->with = [
            'success' => true
        ];

        return $this->collection->map(function ($user) {
            $role = User::getRoleById($user->role_id);

            $courses_teacher = $user
                ->getRelation('coursesTeacher');
            $courses_main_teacher = $user
                ->getRelation('coursesMainTeacher');

            $courses_teacher = collect([
                'teacher' => $courses_teacher,
                'main_teacher' => $courses_main_teacher
            ]);

            $subjects_categories = $courses_teacher
                ->map(function ($courses, $teacher_status) {
                    if ($courses->isEmpty()) return null;
                    return $courses->map(function ($course) use ($teacher_status) {
                        $subject = $course->getRelation('subject');
                        $category = $course->getRelation('category');

                        return [
                            'subject' => [
                                'id' => $course->subject_id,
                                'name' => $subject->name,
                                'role' => $teacher_status,
                            ],
                            'category' => [
                                'id' => $category->id,
                                'name' => $category->name,
                            ],
                        ];
                    });
                })
                ->reject(function ($courses) {
                    return $courses === null;
                })
                ->collapse()
                ->values();

            $subjects = $subjects_categories->pluck('subject')->unique()->values();
            $categories = $subjects_categories->pluck('category')->unique()->values();

            $descriptions = $user
                ->getRelation('teacher_descriptions')
                ->map(function ($teacher_description) {
                    return collect([
                        'id' => $teacher_description->id,
                        'name' => $teacher_description->description,
                    ]);
                });

            return [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $role,
                'email' => $user->email,
                'image' => $user->image,
                'subjects' => $subjects,
                'categories' => $categories,
                'descriptions' => $descriptions,
            ];
        })->toArray();
    }
}
