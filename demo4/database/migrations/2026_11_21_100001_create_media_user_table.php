<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('planned');
            $table->unsignedInteger('progress')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'media_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_user');
    }
};
