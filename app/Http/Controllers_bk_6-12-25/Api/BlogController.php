<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    public function getBlogs()
    {
        $blogs = Blog::select(
            'id',
            'title',
            'category',
            'slug',
            'paragraph',
            'writer_name',
            'read_time',
            'publish_date',
            'image',
            'created_at',
            'updated_at'
        )
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'category' => $blog->category,
                    'slug' => $blog->slug,
                    'paragraph' => $blog->paragraph,
                    'writer_name' => $blog->writer_name,
                    'read_time' => $blog->read_time,
                    'publish_date' => $blog->publish_date ? $blog->publish_date->format('Y-m-d') : null,
                    'image_url' => $blog->image ? asset('storage/blogs/' . $blog->image) : null,
                    'created_at' => $blog->created_at ? $blog->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $blog->updated_at ? $blog->updated_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Blogs fetched successfully.',
            'data' => $blogs
        ]);
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return response()->json([
                'status' => false,
                'message' => 'Blog not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Blog fetched successfully.',
            'data' => [
                'id' => $blog->id,
                'category' => $blog->category,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'paragraph' => $blog->paragraph,
                'writer_name' => $blog->writer_name,
                'read_time' => $blog->read_time,
                'publish_date' => $blog->publish_date ? $blog->publish_date->format('Y-m-d') : null,
                'image_url' => $blog->image ? asset('storage/blogs/' . $blog->image) : null,
                'created_at' => $blog->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
