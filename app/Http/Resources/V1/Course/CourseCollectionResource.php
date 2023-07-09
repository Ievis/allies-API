<?php

namespace App\Http\Resources\V1\Course;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class CourseCollectionResource extends ResourceCollection
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

        return $this->collection->map(function ($course) {
            $main_teacher = $course
                ->getRelation('users')
                ->first()
                ->only([
                    'id',
                    'name',
                    'surname',
                    'image',
                    'description',
                ]);
            $subject = $course
                ->getRelation('subject')
                ->only(['id', 'name']);
            $category = $course
                ->getRelation('category')
                ->only(['id', 'name']);
            $tags = $course
                ->getRelation('tags')
                ->map(function ($tag) {
                    return collect([
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ]);
                });

            return [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'tags' => $tags,
                'main_teacher' => $main_teacher,
                'subject' => $subject,
                'category' => $category,
            ];
        })->toArray();
    }
}
