<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Translation;
class Tag extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    // In Tag model
    public function translations()
    {
        return $this->belongsToMany(Translation::class, 'translation_tag', 'tag_id', 'translation_id');
    }

}
