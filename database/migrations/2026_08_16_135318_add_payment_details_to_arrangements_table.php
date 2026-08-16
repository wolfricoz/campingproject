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
        Schema::table('arrangements', function (Blueprint $table) {
            // How the guest chose to pay, and when the payment was actually registered.
            $table->string('payment_method')->nullable()->after('total_price');
            $table->timestamp('payment_received_at')->nullable()->after('payment_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arrangements', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_received_at']);
        });
    }
};
