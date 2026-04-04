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
    Schema::create('subdivisions', function (Blueprint $table) {
        $table->id();
        
        // Links back to the Land Records table (The Family Tree logic)
        $table->foreignId('parent_lot_id')->constrained('land_records')->cascadeOnDelete();
        $table->foreignId('child_lot_id')->constrained('land_records')->cascadeOnDelete();
        
        $table->date('split_date')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdivisions');
    }
};
