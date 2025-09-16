# Full-Blogging-Platform-Backend-with-Comments-Likes-Objective
Full Blogging Platform Backend with Comments &amp; Likes Objective full site management 
📝 Blogging Platform Backend (Laravel + JWT)

 # A secure and scalable blogging platform backend built with Laravel and MySQL, supporting posts, comments, likes, and user authentication with role-based access control.

🚀 ## Features Implemented
🔐 ## Authentication & Middleware

JWT-based authentication with refresh token support.

Middleware used to enforce role-based permissions.

# Roles:

Admin → Full access to posts & comments.

Author → Can create/update/delete their own posts, manage their comments.

Reader → Can only read published posts & add comments/likes.

📰 # Blog Post Management

CRUD APIs for posts.

Create → Only Admin & Author.

Read → All roles (including Reader).

Search by slug & ID → Admin & Author only.

Update → Admin can update any post, Author can only update their own.

Delete → Admin can delete any post, Author can delete their own.

Each post returns total like count and comment count.

💬#  Comment System

Any authenticated user can add comments.

Supports nested replies.

If a top-level comment is deleted → all child replies auto-deleted.

Edit/Delete Permissions:

User → only their own comments.

Admin → all comments.

👍 # Like System

Users can like/unlike both posts and comments.

Prevents duplicate likes.

Maintains like count on each post/comment.

Toggle API returns updated like count.

🛡️ # Middleware & Role Enforcement

Custom middleware ensures access rules:

jwt → validates JWT token.

role:admin,author → restricts certain routes to Admins & Authors.

User role checked dynamically at request time.

# API Routes
Route::post('/register', [BlogController::class, 'createUser']);
Route::post('/login', [BlogController::class, 'login']);
Route::post('/logout', [BlogController::class, 'logout']);
Route::middleware([jwtMiddleware::class])->get('/profile', [BlogController::class, 'profile']);

// 📰 Posts (Admin & Author only)
Route::middleware(['jwt', 'role:admin,author'])->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/all-posts', [PostController::class, 'all']);
    Route::get('/post/{slug}', [PostController::class, 'showWithSlug']);
    Route::get('/post/id/{id}', [PostController::class, 'showWithId']);
    Route::put('/post/update/{id}', [PostController::class, 'update']);
    Route::delete('/post/delete/{id}', [PostController::class, 'destroy']);
});

// 💬 Comments & 👍 Likes (All authenticated users)
Route::middleware(['jwt'])->group(function () {
    Route::get('/all-published-posts', [PostController::class, 'index']);
    Route::post('/add-comments', [CommentController::class, 'store']);
    Route::put('/edit-comment/{id}', [CommentController::class, 'editComment']);
    Route::post('/reply-comment/{id}', [CommentController::class, 'replyComment']);
    Route::delete('/delete-comment/{id}', [CommentController::class, 'deleteComment']);
    Route::post('/like-toggle', [LikeController::class, 'toggleLike']);
});


# Database Schema (Core Tables)
All database sechema in the migration

# Deliverables

 # Future Improvements

Tagging & Categories → Better content organization.

Popular Posts API → Sort by likes/comments.
✅ Source Code (GitHub Repository)
Complete backend implementation uploaded on GitHub with commit history.

✅ API Collection (Postman/Insomnia)
Exported Postman collection containing all implemented routes (Auth, Posts, Comments, Likes).

✅ Database Schema (via Laravel Migrations)
Database tables are fully managed using Laravel migrations (users, posts, comments, likes, etc.).
No separate diagram created, but schema can be generated directly from migrations.

✅ Hosted Live API
Currently not deployed on any hosting platform (e.g., Railway, Render, Heroku). Runs locally for development and testing.
Advanced Search & Pagination → By title/content.

Admin Moderation Tools → Approve/reject before publish.

User Profiles → Show posts, comments, likes history.
