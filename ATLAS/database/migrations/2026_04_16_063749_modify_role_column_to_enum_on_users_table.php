<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, clean up invalid role values
        // Update empty/NULL roles to 'user'
        DB::statement("UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''");
        
        // Update 'land_management_officer' to 'land_officer' for consistency with the app
        DB::statement("UPDATE users SET role = 'land_officer' WHERE role = 'land_management_officer'");
        
        // Now apply the enum constraint
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'records_officer', 'land_officer', 'user'])
                  ->default('user')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to string type
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });
    }
};
