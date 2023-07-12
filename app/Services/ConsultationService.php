<?php

namespace App\Services;

use App\Models\Consultation;

class ConsultationService
{
    public static function createConsultation(array $data): Consultation
    {
        return Consultation::create($data);
    }

    public static function updateConsultation(array $data, Consultation $consultation): Consultation
    {

    }

    public static function deleteConsultation(Consultation $consultation): Consultation
    {

    }
}
