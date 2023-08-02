<?php

namespace App\Services;

use App\Models\Section;

class SectionService
{
    public static function createSection(array $data): Section
    {
        return Section::create($data);
    }

    public static function updateSection(array $data, Section $section): Section
    {
        $section->update($data);

        return $section;
    }

    public static function deleteSection(Section $section): Section
    {
        $section->delete();

        return $section;
    }
}
