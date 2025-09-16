<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comments;
use App\Models\Like;

class LikeController extends Controller
{
    public function toggleLike(Request $request)
    {
        try {
            $user = $request->attributes->get('user');
            $item_id = $request->item_id; //post or comment
            $item_type = $request->item_type; //post or comment
            if (!$item_id || !$item_type) {
                return response()->json([
                    'error' => 'item_id and item_type are required'
                ], 400);
            }
            if (!in_array($item_type, ['post', 'comment'])) {
                return response()->json([
                    'error' => 'item_type must be either post or comment'
                ], 400);
            }
            $likeModel = ($item_type == 'post') ? Post::class : Comments::class;
            $item = $likeModel::find($item_id);
            if (!$item) {
                return response()->json([
                    'error' => ucfirst($item_type) . ' not found'
                ], 404);
            }
            //if like exists remove it else add it
            $like = Like::where('likeable_id', $item_id)->where('likeable_type', $likeModel)->where('user_id', $user->id)->first();
            if ($like) {
                $like->delete();
                $likeCount = Like::where('likeable_id', $item_id)->where('likeable_type', $likeModel)->count();
                $item->like_count = $likeCount;
                $item->save();
                return response()->json([
                    'status' => 'success',
                    'message' => ucfirst($item_type) . ' unliked'
                ], 200);
            } else {
                $newLike = new Like;
                $newLike->user_id = $user->id;
                $newLike->likeable_id = $item_id;
                $newLike->likeable_type = $likeModel;
                $newLike->save();
                $likeCount = Like::where('likeable_id', $item_id)->where('likeable_type', $likeModel)->count();
                $item->like_count = $likeCount;
                $item->save();
                return response()->json([
                    'status' => 'success',
                    'message' => ucfirst($item_type) . ' liked'
                ], 201);
            }
            // $existingLike=$likeModel::where('user_id',$user->id)->where('item_id',$item_id)->first();
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
}
