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
        Schema::table('kitchen_mass_productions', function (Blueprint $table) {
             // 🔹 Add remarks column
            $table->string('remarks')->nullable()->after('quantity');

            // 🔹 Add created_by column with FK to users
            $table->unsignedBigInteger('created_by')->nullable()->after('remarks');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_mass_productions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'remarks']);
        });
    }
};
