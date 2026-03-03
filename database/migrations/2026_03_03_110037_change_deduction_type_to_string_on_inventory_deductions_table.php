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
        Schema::table('inventory_deductions', function (Blueprint $table) {
            // Change deduction_type from ENUM to string(50)
            $table->string('deduction_type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_deductions', function (Blueprint $table) {
            // Revert back to previous ENUM (replace with your original ENUM options)
            $table->enum('deduction_type', ['waste', 'manual'])->change();
        });
    }
};
