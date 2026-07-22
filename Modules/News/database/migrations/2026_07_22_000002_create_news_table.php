<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 160);
            $table->string('slug', 160)->unique();
            $table->string('excerpt', 500);
            $table->longText('body_markdown');
            $table->string('cover_path');
            $table->foreignId('category_id')->constrained('news_categories')->restrictOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
