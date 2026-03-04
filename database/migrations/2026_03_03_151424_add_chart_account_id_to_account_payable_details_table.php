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
        Schema::table('account_payable_details', function (Blueprint $table) {
            $table->unsignedBigInteger('chart_account_id')
                  ->after('id'); // or place after a specific column

            $table->foreign('chart_account_id')
                  ->references('id')
                  ->on('chart_accounts')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_payable_details', function (Blueprint $table) {
             $table->dropForeign(['chart_account_id']);
            $table->dropColumn('chart_account_id');
        });
    }
};
