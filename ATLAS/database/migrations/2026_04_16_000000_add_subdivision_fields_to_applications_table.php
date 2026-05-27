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
        Schema::table('applications', function (Blueprint $table) {
            // Lot Type: 'existing_lot' or 'subdivision'
            $table->enum('lot_type', ['existing_lot', 'subdivision'])->nullable()->after('date_received');
            
            // Subdivision Fields (only populated if lot_type = 'subdivision')
            $table->string('new_lot_number')->nullable()->after('lot_type');
            $table->decimal('subdivided_area', 10, 2)->nullable()->after('new_lot_number');
            $table->decimal('remaining_area', 10, 2)->nullable()->after('subdivided_area');
            
            // Land Officer Assessment Details
            $table->text('land_officer_remarks')->nullable()->after('remaining_area');
            $table->foreignId('land_officer_id')->nullable()->constrained('users')->nullOnDelete()->after('land_officer_remarks');
            $table->timestamp('assessed_at')->nullable()->after('land_officer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['land_officer_id']);
            $table->dropColumn([
                'lot_type',
                'new_lot_number',
                'subdivided_area',
                'remaining_area',
                'land_officer_remarks',
                'land_officer_id',
                'assessed_at'
            ]);
        });
    }
};
