<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       DB::table('components')->update(['unit' => null]);
       Schema::table('components', function (Blueprint $table) {
            $table->renameColumn('unit', 'unit_id');
            $table->unsignedBigInteger('unit_id')->nullable()->change();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);

            if (Schema::hasColumn('components', 'unit_id')) {
                $table->renameColumn('unit_id', 'unit');
            }
        });
    }
};
