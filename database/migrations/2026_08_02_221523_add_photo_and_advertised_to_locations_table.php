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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('description'); // pad naar de afbeelding, bijv. /images/chalet.jpg
            $table->boolean('is_advertised')->default(false)->after('status'); // toont de locatie op de homepage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['photo', 'is_advertised']);
        });
    }
};
