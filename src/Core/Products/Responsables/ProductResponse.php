<?php

namespace App\Http\Responses;

class ProductResponse extends Response
{
    public function __construct($posts)
    {
        $this->posts = $posts;
    }

    public function toResponse()
    {
        return response()->json($this->transformPosts());
    }

    protected function transformPosts()
    {
        return $this->posts->map(function ($post) {
            return [
                'title' => $post->title,
                'description' => $post->description,
                'body' => $post->body,
                'published_date' => $post->published_at->toIso8601String(),
                'created' => $post->created_at->toIso8601String(),
            ];
        });
    }
}
