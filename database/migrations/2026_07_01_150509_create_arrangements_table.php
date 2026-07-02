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
        Schema::create('arrangements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('total_price', 8, 2)->nullable();
            $table->string('booking_status')->default('pending'); // pending, confirmed, cancelled
            $table->string('source')->default('website');
            $table->boolean('confirmation_email_sent')->default(false);
            $table->boolean('payment_received')->default(false);
            $table->integer('status')->default(1);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arrangements');
    }
};
