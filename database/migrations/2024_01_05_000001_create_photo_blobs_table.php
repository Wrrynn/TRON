<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('photo_blobs')) {
            return;
        }

        Schema::create('photo_blobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foto_id')->index();
            $table->string('mime', 100)->default('image/jpeg');
            $table->longText('data'); // gambar disimpan base64 (persisten di serverless)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_blobs');
    }
};
