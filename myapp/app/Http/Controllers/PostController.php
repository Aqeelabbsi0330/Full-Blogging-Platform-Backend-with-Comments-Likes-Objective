<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //all published posts
    public function index()
    {
        try {
            $posts = Post::where('status', 'published')->get();
            $data = [];
            foreach ($posts as $post) {
                $data[] = [
                    'post_title' => $post->title,
                    'post_body' => $post->body,
                    'post_status' => $post->status,
                    'post_slug' => $post->slug,
                    'post_excerpt' => $post->excerpt,
                    'post_user_id' => $post->user_id
                ];
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    //all posts including drafts and unpublished
    public function all()
    {
        try {
            $posts = Post::all();
            $data = [];
            foreach ($posts as $post) {
                $data[] = [
                    'post_title' => $post->title,
                    'post_body' => $post->body,
                    'post_status' => $post->status,
                    'post_slug' => $post->slug,
                    'post_excerpt' => $post->excerpt,
                    'post_user_id' => $post->user_id
                ];
            }
            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    public function store(Request $request)
    {
        $user = $request->attributes->get('user');
        $title = request('title');
        $body = request('body');
        $status = request('status');
        $slug = Str::slug($title, '-');
        $excerpt = substr($body, 0, 100);
        $post = new Post;
        // $post->title = $title;
        // $post->body = $body;
        // $post->status = $status;
        // $post->slug = $slug;
        // $post->excerpt = $excerpt;
        // $post->user_id = $user->id;
        // $post->created_by = $user->id;
        $post->fill([
            'title'      => $title,
            'body'       => $body,
            'status'     => $status,
            'slug'       => $slug,
            'excerpt'    => $excerpt,
            'user_id'    => $user->id,
            'created_by' => $user->id,
        ]);
        $post->save();
        return response()->json([
            'status' => 'success',
            'data' => [
                'post_title' => $post->title,
                'post_body' => $post->body,
                'post_status' => $post->status,
                'post_slug' => $post->slug,
                'post_excerpt' => $post->excerpt,
                'post_user_id' => $post->user_id
            ]
        ], 201);
    }
    //single post by slug
    public function showWithSlug($slug)
    {
        try {
            $post = Post::where('slug', $slug)->first();
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'post not found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => [
                    'post_title' => $post->title,
                    'post_body' => $post->body,
                    'post_status' => $post->status,
                    'post_slug' => $post->slug,
                    'post_excerpt' => $post->excerpt,
                    'post_user_id' => $post->user_id
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    //single post by id
    public function showWithId($id)
    {
        try {
            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'post not found'
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => [
                    'post_title' => $post->title,
                    'post_body' => $post->body,
                    'post_status' => $post->status,
                    'post_slug' => $post->slug,
                    'post_excerpt' => $post->excerpt,
                    'post_user_id' => $post->user_id
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('user');
            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'post not found'
                ], 404);
            }
            //first check if the user is author then match the user id with the post user id
            $role = $user->role->name;
            if ($role === 'author') {
                if ($post->user_id !== $user->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'you are not authorized to update this post'
                    ], 403);
                }
            }
            //second check if the user is admin then allow to update any post
            //only admin and author reach this code form middleware


            // if($post->user_id !== $user->id){
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'you are not authorized to update this post'
            //     ],403);
            // }//commented for the admin to update any post
            $title = request('title');
            $body = request('body');
            $status = request('status');
            $slug = Str::slug($title, '-');
            $excerpt = substr($body, 0, 100);
            $post->fill([
                'title' => $title,
                'body' => $body,
                'status' => $status,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'user_id' => $user->id,
                'created_by' => $user->id,
                'updated_by' => $user->id
            ]);
            $post->save();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'post_title' => $post->title,
                    'post_body' => $post->body,
                    'post_status' => $post->status,
                    'post_slug' => $post->slug,
                    'post_excerpt' => $post->excerpt,
                    'post_user_id' => $post->user_id
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    //delete post by id only admin can delete and author can delete their own posts
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('user');
            $post = Post::find($id);
            if (!$post) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'post not found'
                ], 404);
            }
            $role = $user->role->name;
            if ($role === 'author') {
                if ($post->user_id !== $user->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'you are not authorized to delete this post'
                    ], 403);
                }
            }
            $post->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'post deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }

}
