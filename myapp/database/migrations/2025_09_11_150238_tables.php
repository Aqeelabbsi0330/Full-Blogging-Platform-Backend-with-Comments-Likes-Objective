<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Post ka author (User se relation)

            $table->string('title'); // Post title
            $table->string('slug')->unique(); // SEO friendly slug
            $table->longText('body'); // Markdown/HTML content
            $table->text('excerpt')->nullable(); // Short summary

            $table->enum('status', ['draft', 'published'])->default('draft');
            // Post ka status

            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);

            // Created / Updated tracking
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // deleted_at
        });
        Schema::create(
            'comments',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('post_id')
                    ->constrained('posts')
                    ->onDelete('cascade');

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->onDelete('cascade');

                $table->unsignedBigInteger('parent_id')->nullable();
                $table->foreign('parent_id')
                    ->references('id')->on('comments')
                    ->onDelete('cascade');

                $table->longText('body');
                $table->unsignedBigInteger('like_count')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();
                $table->softDeletes();
            }
        );
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->unsignedBigInteger('likeable_id');
            $table->string('likeable_type');

            $table->timestamps();

            // Prevent duplicate likes by the same user on the same entity
            $table->unique(['user_id', 'likeable_id', 'likeable_type']);
        });
        Schema::create('tags', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name')->unique(); // Unique tag name
            $table->timestamps(); // created_at, updated_at
        });
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id(); // optional, Laravel prefers PK
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');

            $table->unique(['post_id', 'tag_id']); // prevent duplicate entries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('post_tag');
    }
};
