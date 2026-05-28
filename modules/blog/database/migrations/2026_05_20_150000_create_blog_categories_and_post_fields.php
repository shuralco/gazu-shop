<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full-blog support: blog categories (рубрики) + blog-specific fields on the
 * `pages` table (posts are Page records with template=blog_post).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blog_categories')) {
            Schema::create('blog_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');                       // translatable JSON
                $table->string('slug', 120)->unique();
                $table->text('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 320)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('pages', function (Blueprint $table) {
            if (! Schema::hasColumn('pages', 'blog_category_id')) {
                $table->foreignId('blog_category_id')->nullable()->after('template')
                    ->constrained('blog_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('pages', 'author')) {
                $table->string('author')->nullable()->after('blog_category_id');
            }
            if (! Schema::hasColumn('pages', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('author');
            }
            if (! Schema::hasColumn('pages', 'views')) {
                $table->unsignedInteger('views')->default(0)->after('published_at');
            }
            if (! Schema::hasColumn('pages', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('views');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            foreach (['author', 'published_at', 'views', 'is_featured'] as $col) {
                if (Schema::hasColumn('pages', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('pages', 'blog_category_id')) {
                // drop FK then column (guarded for sqlite)
                try {
                    $table->dropConstrainedForeignId('blog_category_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('blog_category_id');
                }
            }
        });

        Schema::dropIfExists('blog_categories');
    }
};
