<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Resources\Json\JsonResource;

class Article extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'thumbnail' => url($this->file->path),
            'created_at' => $this->created_at->format('d-m-Y H:i'),
            'link' => route('article', [ 'article' => $this->id ]),
            'link_short' => route('article.short', [ 'article' => $this->id ])
        ];
    }
}
