<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('anilist_id')->nullable()->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('cover_url')->nullable();
            $table->string('trailer_url', 2048)->nullable();
            $table->text('synopsis')->nullable();
            $table->unsignedSmallInteger('episodes')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('score', 3, 1)->nullable();
            $table->json('genres')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
