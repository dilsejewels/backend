<?php

namespace App\Http\Controllers\Jewellery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BlogController extends Controller
{
    /**
     * Show the blog management page
     */
    public function index()
    {
        return view('admin.Jewellery.blog.index');
    }

    /**
     * Fetch all blogs for DataTable
     */
    public function fetch()
    {
        try {
            $blogs = Blog::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $blogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching blogs'
            ], 500);
        }
    }

    /**
     * Store a new blog (CREATE method)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'category' => 'required|in:Engagement Rings,Gemstone Insights,Wedding Bands,Metal,Buying Guides,Diamond,Jewelry',
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'paragraph' => 'required|string',
                'writer_name' => 'nullable|string|max:255',
                'read_time' => 'nullable|string|max:50',
                'publish_date' => 'nullable|date'
            ]);

            // Generate slug from title
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            // Check if slug already exists
            while (Blog::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $blogData = [
                'category' => $request->category,
                'title' => $request->title,
                'slug' => $slug,
                'paragraph' => $request->paragraph,
                'writer_name' => $request->writer_name,
                'read_time' => $request->read_time,
                'publish_date' => $request->publish_date ?: Carbon::now()->format('Y-m-d')
            ];

            // Handle image upload - store only image name
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('blogs', $imageName, 'public');
                $blogData['image'] = $imageName; // Store only image name
            }

            $blog = Blog::create($blogData);

            return response()->json([
                'success' => true,
                'message' => 'Blog created successfully',
                'data' => $blog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating blog: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single blog details
     */
    public function show($id)
{
    try {
        $blog = Blog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Blog not found'
        ], 404);
    }
}


    /**
     * Update a blog
     */
    public function update(Request $request, $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            $request->validate([
                'category' => 'required|in:Engagement Rings,Gemstone Insights,Wedding Bands,Metal,Buying Guides,Diamond,Jewelry',
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'paragraph' => 'required|string',
                'writer_name' => 'nullable|string|max:255',
                'read_time' => 'nullable|string|max:50',
                'publish_date' => 'nullable|date'
            ]);

            // Generate new slug if title changed
            if ($blog->title != $request->title) {
                $slug = Str::slug($request->title);
                $originalSlug = $slug;
                $counter = 1;

                while (Blog::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $blog->slug = $slug;
            }

            $blog->category = $request->category;
            $blog->title = $request->title;
            $blog->paragraph = $request->paragraph;
            $blog->writer_name = $request->writer_name;
            $blog->read_time = $request->read_time;
            $blog->publish_date = $request->publish_date;

            // Handle image upload - store only image name
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($blog->image) {
                    Storage::disk('public')->delete('blogs/' . $blog->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('blogs', $imageName, 'public');
                $blog->image = $imageName; // Store only image name
            }

            $blog->save();

            return response()->json([
                'success' => true,
                'message' => 'Blog updated successfully',
                'data' => $blog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating blog: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a blog
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);

            // Delete image if exists
            if ($blog->image) {
                Storage::disk('public')->delete('blogs/' . $blog->image);
            }

            $blog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Blog deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting blog'
            ], 500);
        }
    }
}
