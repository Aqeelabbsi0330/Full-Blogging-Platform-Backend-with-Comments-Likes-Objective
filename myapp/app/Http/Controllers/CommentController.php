<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comments;
use App\Models\Post;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = $request->attributes->get('user');
            $post_id = $request->input('post_id');
            // $parent_id=$request->parent_id;
            $body = $request->input('body');
            if (!$post_id || !$body) {
                return response()->json([
                    'error' => 'post_id and body are required'
                ], 400);
            }
            $comment = new Comments;
            $comment->post_id = $post_id;
            $comment->user_id = $user->id;
            // $comment->parent_id=$parent_id;
            $comment->body = $body;
            $comment->created_by = $user->id;
            $comment->updated_by = $user->id;
            $comment->save();
            $count = Comments::where('post_id', $post_id)->count();
            $post = Post::find($post_id);
            $post->comments_count = $count;
            $post->save();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'comment_id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'user_id' => $comment->user_id,
                    'body' => $comment->body,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    // edit comment by id only the owner of the comment can edit and admin can edit
    public function editComment(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('user');
            $comment = Comments::find($id);
            if (!$comment) {
                return response()->json([
                    'error' => 'Comment not found'
                ], 404);
            }
            if ($comment->user_id != $user->id && $user->role_id != 1) {
                return response()->json([
                    'error' => 'You are not authorized to edit this comment'
                ], 403);
            }
            $body = $request->input('body');
            if (!$body) {
                return response()->json([
                    'error' => 'body is required'
                ], 400);
            }
            $comment->body = $body;
            $comment->updated_by = $user->id;
            $comment->save();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'comment_id' => $comment->id,
                    'post_id' => $comment->post_id,
                    'user_id' => $comment->user_id,
                    'body' => $comment->body,
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
    //reply to a comment
    public function replyComment(Request $request, $id)
    {
        try {
            $User = $request->attributes->get('user');
            $parentComment = Comments::find($id);
            if (!$parentComment) {
                return response()->json([
                    'error' => 'Parent comment not found'
                ], 404);
            }
            $body = $request->input('body');
            if (!$body) {
                return response()->json([
                    'error' => 'body is required'
                ], 400);
            }
            $replyComment = new Comments;
            $replyComment->post_id = $parentComment->post_id;
            $replyComment->user_id = $User->id;
            $replyComment->parent_id = $parentComment->id;
            $replyComment->body = $body;
            $replyComment->created_by = $User->id;
            $replyComment->updated_by = $User->id;
            $replyComment->save();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'comment_id' => $replyComment->id,
                    'post_id' => $replyComment->post_id,
                    'user_id' => $replyComment->user_id,
                    'parent_id' => $replyComment->parent_id,
                    'body' => $replyComment->body,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode()
            ], 404);
        }
    }
    public function deleteComment(Request $request, $id)
    {
        try {
            $user = $request->attributes->get('user');
            $comment = Comments::find($id);

            if (!$comment) {
                return response()->json([
                    'error' => 'Comment not found'
                ], 404);
            }
            if ($comment->user_id != $user->id && $user->role_id != 1) {
                return response()->json([
                    'error' => 'You are not authorized to delete this comment'
                ], 403);
            }
            // Recursively delete replies
            $this->deleteReplies($comment);
            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                $e->getFile(),
                $e->getCode(),
                $e->getLine()
            ], 404);
        }
    }
    private function deleteReplies($comment)
    {
        foreach ($comment->replies as $reply) {
            $this->deleteReplies($reply);
        }
        $comment->delete();
    }
    //total comments count on a post
    // public function totalCommentsCount($post_id)
    // {
    //     try {
    //         $count = Comments::where('post_id', $post_id)->count();
    //         if (!$post_id) {
    //             return response()->json([
    //                 'error' => 'post_id is required'
    //             ], 400);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'post_id' => $post_id,
    //             'total_comments' => $count
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $e->getMessage(),
    //             $e->getFile(),
    //             $e->getCode()
    //         ], 404);
    //     }
    // }
}
