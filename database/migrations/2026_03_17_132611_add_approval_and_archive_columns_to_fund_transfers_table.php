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
        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->dateTime('approved_datetime')->nullable()->after('approved_by');
            $table->unsignedBigInteger('archived_by')->nullable()->after('approved_datetime');
            $table->dateTime('archived_datetime')->nullable()->after('archived_by');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['archived_by']);

            // Drop the columns
            $table->dropColumn(['approved_by', 'approved_datetime', 'archived_by', 'archived_datetime']);
        });
    }
};
