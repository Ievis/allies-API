<?php

namespace App\Http\Resources\V1\Review;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReviewCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($review) {
            $user = $review->getRelation('user');

            $review_results = $review
                ->getRelation('results')
                ->map(function ($review_result) {
                    return $review_result->only([
                        'id',
                        'result_description',
                    ]);
                });

            return [
                'id' => $review->id,
                'review' => $review->review,
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'image' => $user->image,
                ],
                'review_results' => $review_results
            ];
        })->toArray();
    }
}
