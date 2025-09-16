<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Middleware\jwtMiddleware;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/test', function () {
    return response()->json(['message' => 'API working!']);
});
Route::post('/register', [BlogController::class, 'createUser']);
Route::post('/login', [BlogController::class, 'login']);
Route::post('/logout', [BlogController::class, 'logout']);
Route::middleware([jwtMiddleware::class])->group(function () {
    Route::get('/profile', [BlogController::class, 'profile']);
});
Route::middleware(['jwt', 'role:admin,author'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    //all published posts
    // Route::get('/all-published-posts', [PostController::class, 'index']);
    //all posts including drafts and unpublished
    Route::get('/all-posts', [PostController::class, 'all']);
    //single post by slug
    Route::get('/post/{slug}', [PostController::class, 'showWithSlug']);
    //single post by id
    Route::get('/post/id/{id}', [PostController::class, 'showWithId']);
    //update post by id only admin and author can update . author can update only their own posts
    // admin can update any post
    Route::put('/post/update/{id}', [PostController::class, 'update']);
    //delete post by id only admin can delete
    Route::delete('/post/delete/{id}', [PostController::class, 'destroy']);
});
//comments
//any authenticated user can add comment
Route::middleware(['jwt'])->group(function () {
    Route::get('/all-published-posts', [PostController::class, 'index']);
    Route::post('/add-comments', [App\Http\Controllers\CommentController::class, 'store']);
    Route::put('/edit-comment/{id}', [CommentController::class, 'editComment']);
    Route::post('/reply-comment/{id}', [CommentController::class, 'replyComment']);
    Route::delete('/delete-comment/{id}', [CommentController::class, 'deleteComment']);
    Route::post('/like-toggle', [App\Http\Controllers\LikeController::class, 'toggleLike']);
});
