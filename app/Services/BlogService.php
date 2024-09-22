<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogService
{

    public function getBlogs(Request $request)
    {
        $blogs = Blog::published();

        $blogs->when(
            $request->has('category_id'),
            fn($q) => $q->where('blog_category_id', $request->get('category_id'))
        );

        $blogs->when(
            $request->has('tag_id'),
            fn($q) => $q->whereHas('tags', fn($q) => $q->where('blog_tag_id', $request->get('tag_id')))
        );

        $blogs->when(
            $request->has('search'),
            fn($q) => $q->where('title', 'like', "%{$request->get('search')}%")
                ->orWhereHas('category', fn($q) => $q->where('name', 'like', "%{$request->get('search')}%"))
                ->orWhereHas('tags', fn($q) => $q->where('name', 'like', "%{$request->get('search')}%"))
        );

        $blogs = $blogs->latest('id')->paginate(10);

        return $blogs;
    }
}
