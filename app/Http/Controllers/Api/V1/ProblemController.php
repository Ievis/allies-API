<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostProblemRequest;
use App\Http\Resources\V1\Problem\ProblemCollectionResource;
use App\Http\Resources\V1\Problem\ProblemResource;
use App\Models\Problem;
use App\Services\ProblemService;
use Illuminate\Http\Request;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        $problems = Problem::filter($request->all())->simplePaginateFilter($request->per_page ?? Problem::count());

        return new ProblemCollectionResource($problems);
    }

    public function store(PostProblemRequest $request): ProblemResource
    {
        $data = $request->validated();
        $problem = ProblemService::createProblem($data);

        return new ProblemResource($problem);
    }

    public function show(Problem $problem): ProblemResource
    {
        return new ProblemResource($problem);
    }

    public function update(PostProblemRequest $request, Problem $problem): ProblemResource
    {
        $data = $request->validated();
        $problem = ProblemService::updateProblem($problem, $data);

        return new ProblemResource($problem);
    }

    public function delete(Problem $problem)
    {
        $deleted_problem = ProblemService::deleteProblem($problem);

        return new ProblemResource($deleted_problem);
    }
}
