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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique()->default(DB::raw('gen_random_uuid()'));

            $table->string('name');
            $table->string('type')->nullable(); // e.g. tent pitch, caravan pitch, cabin, RV spot
            $table->text('description')->nullable();

            $table->Integer('capacity')->nullable(); // max guests
            $table->integer('bedrooms')->default(1);
            $table->decimal('size', 8, 2)->nullable(); // e.g. m²
            $table->decimal('price_per_night', 8, 2)->nullable();

            $table->boolean('has_electricity')->default(false);
            $table->boolean('has_water')->default(false);
            $table->boolean('has_shade')->default(false);

            $table->integer('status')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
