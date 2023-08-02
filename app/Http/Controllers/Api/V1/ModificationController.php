<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostModificationRequest;
use App\Http\Resources\V1\Lesson\LessonResource;
use App\Http\Resources\V1\Modification\ModificationCollectionResource;
use App\Http\Resources\V1\Modification\ModificationResource;
use App\Http\Resources\V1\Problem\ProblemResource;
use App\Models\Lesson;
use App\Models\Modification;
use App\Services\ModificationService;
use App\Services\PreModificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\AssignOp\Mod;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModificationController extends Controller
{
    public function index(Request $request): ModificationCollectionResource
    {
        $modifications = Modification::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Modification::count());

        return new ModificationCollectionResource($modifications);
    }

    public function show(Modification $modification): ModificationResource
    {
        return new ModificationResource($modification);
    }

    public function update(PostModificationRequest $request, Modification $modification): LessonResource|ProblemResource
    {
        $decision = $request->validated('decision');
        if ($modification->is_resolved) throw new NotFoundHttpException();
        $modifiable_model = $modification->modifiable()->first();
        $modifiable_model_data = $modifiable_model->toArray();
        $modifiable_current_id = $modification->current_id;
        $model_id = $modifiable_current_id === $modifiable_model->id
            ? null
            : $modifiable_current_id;
        $user = auth()->user();

        $modification_service = new ModificationService($modifiable_model_data, $user, $modifiable_model, $model_id);
        $modification_service->setModification($modification);

        $modified_model = $modification_service->resolveModification($decision);

        return $modified_model::class === Lesson::class
            ? new LessonResource($modified_model)
            : new ProblemResource($modified_model);
    }

    public function delete(Modification $modification): ModificationResource
    {
        $model = $modification->modifiable()->first();
        $model->forceDelete();
        $modification->delete();

        return new ModificationResource($modification);
    }
}
