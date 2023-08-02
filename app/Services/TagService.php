<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagService
{
    public static function createTag(array $data): Tag
    {
        $model_name = 'App\\Models\\' . ucfirst(strtolower($data['for']));
        $tag = Tag::create(['name' => $data['name']]);

        DB::table('taggables')->insert([
            'taggable_type' => $model_name,
            'taggable_id' => $data['id'],
            'tag_id' => $tag->id,
            'created_at' =>  \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);

        return $tag;
    }

    public static function updateTag(array $data, Tag $tag): Tag
    {
        $tag->update(['name' => $data['name']]);

        return $tag;
    }

    public static function deleteTag(Tag $tag): Tag
    {
        $tag->forceDelete();

        return $tag;
    }
}
