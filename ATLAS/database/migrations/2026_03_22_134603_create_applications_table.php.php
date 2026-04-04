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
        Schema::create('applications', function (Blueprint $table) {
            $table->id(); 
            $table->string('tracking_no')->unique();
            
            // Foreign Keys linking to the other tables
            $table->foreignId('applicant_id')->constrained('applicants')->cascadeOnDelete();
            $table->foreignId('land_record_id')->constrained('land_records')->cascadeOnDelete();
            
            $table->date('date_received');

            // MENTOR's REQUIREMENT (Updated):
            // Defaults to the word 'Patent' if no exact number is typed.
            $table->string('patent_details')->default('Patent'); 
            $table->string('patent_type')->nullable(); // e.g., Agricultural Free Patent, Residential
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
