<?php

namespace App\Models\Page;

use App\Models\Page\Traits\Scopes\PageScope;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use PageScope;

    public $fillable = [
        'title',
        'slug',
        'content',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
