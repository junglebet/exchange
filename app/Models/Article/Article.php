<?php

namespace App\Models\Article;

use App\Models\Article\Traits\Relations\ArticleRelation;
use App\Models\Article\Traits\Scopes\ArticleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    public $table = 'articles';

    use HasFactory, SoftDeletes, ArticleRelation, ArticleScope;

    public $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'featured',
        'language',
        'file_id',
        'category_id',
    ];

    protected $casts = [
        'status' => 'boolean',
        'featured' => 'boolean',
    ];
}
