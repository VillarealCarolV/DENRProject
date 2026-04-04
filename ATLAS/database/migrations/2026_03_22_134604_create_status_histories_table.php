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
    Schema::create('status_histories', function (Blueprint $table) {
        $table->id();
        
        // Links to the Application
        $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
        
        // The Status Toggle we discussed in the flowchart
        $table->enum('status', ['Pending', 'In Process', 'Approved', 'Rejected'])->default('Pending');
        $table->text('remarks')->nullable();
        $table->string('updated_by')->nullable(); // Who changed the status
        
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_histories');
    }
};
