<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Review\ReviewCollectionResource;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): ReviewCollectionResource
    {
        $reviews = Review::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Review::count());

        return new ReviewCollectionResource($reviews);
    }
}
