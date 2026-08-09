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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique();
            $table->string('title', 100);
            $table->string('slug');
            $table->string('summary')->nullable();
            $table->text('content');
            $table->string('image')->nullable();
            $table->string('type')->default('Algemeen');
            $table->boolean('published')->default(false);
            $table->timestamps();
            $table->integer('status')->default(1);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
