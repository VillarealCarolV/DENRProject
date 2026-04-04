<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('land_records', function (Blueprint $table) {
        $table->id(); // This acts as lot_id
        $table->string('survey_no')->unique();
        $table->decimal('total_area', 10, 2); // Handles numbers like 1000.50 sqm
        $table->string('location');
        $table->boolean('is_subdivided')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_records');
    }
};
