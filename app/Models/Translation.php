<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;
class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['group', 'key', 'locale', 'value'];

    // In Translation model
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'translation_tag', 'translation_id', 'tag_id');
    }

}
