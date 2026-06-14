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
        Schema::table('land_records', function (Blueprint $table) {
            $table->char('lot_letter', 1)->nullable()->after('survey_no')->comment('Lot letter (A, B, C, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('land_records', function (Blueprint $table) {
            $table->dropColumn('lot_letter');
        });
    }
};
