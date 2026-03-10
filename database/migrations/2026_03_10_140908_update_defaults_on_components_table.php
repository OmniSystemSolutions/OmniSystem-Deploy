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
        Schema::table('components', function (Blueprint $table) {
             $table->decimal('cost', 15, 2)->default(0.00)->change();
            $table->decimal('price', 15, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->default(null)->change();
            $table->decimal('price', 15, 2)->default(null)->change();
        });
    }
};
