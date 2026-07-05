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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('guid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->timestamps();
            $table->string('name');
            $table->string('email');
            $table->string('phone_number');
            $table->string('street_name');
            $table->string('street_number');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('status')->default(1); // 1 = active, 2 = inactive, 3 = archive
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
