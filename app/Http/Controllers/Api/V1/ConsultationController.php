<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostConsultationRequest;
use App\Http\Resources\V1\Consultation\ConsultationResource;
use App\Services\ConsultationService;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function store(PostConsultationRequest $request): ConsultationResource
    {
        $data = $request->validated();
        $consultation = ConsultationService::createConsultation($data);

        return new ConsultationResource($consultation);
    }
}
