<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::where('published', true)
                         ->orderBy('published_at', 'desc');
        
        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by tag if provided
        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }
        
        $posts = $query->paginate(12);
        
        return view('frontend.blog.index', compact('posts'));
    }
    
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
                       ->where('published', true)
                       ->firstOrFail();
        
        // Get related posts
        $relatedPosts = BlogPost::where('published', true)
                               ->where('id', '!=', $post->id)
                               ->where('category', $post->category)
                               ->orderBy('published_at', 'desc')
                               ->limit(3)
                               ->get();
        
        return view('frontend.blog.show', compact('post', 'relatedPosts'));
    }
}
