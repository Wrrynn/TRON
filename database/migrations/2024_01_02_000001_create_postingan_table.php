<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('location');
            $table->text('story')->nullable();
            $table->text('destinations')->nullable();
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->date('travel_date')->nullable();
            $table->timestamps();
        });

        Schema::create('post_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_post_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('travel_post_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('score');
            $table->unique(['user_id', 'travel_post_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('post_photos');
        Schema::dropIfExists('travel_posts');
    }
};