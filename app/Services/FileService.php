<?php

namespace App\Services;

class FileService
{
    public static function save($file)
    {
        if (is_string($file)) return $file;
        return empty($file)
            ? url('images/avatar-default.png')
            : url($file->store('images'));
    }
}
