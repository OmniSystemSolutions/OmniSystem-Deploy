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
        Schema::create('kitchen_mass_productions', function (Blueprint $table) {
            $table->id();
            // Product reference
        $table->foreignId('product_id')
              ->constrained('products')
              ->cascadeOnDelete();

        // Production details
        $table->integer('quantity');
        $table->enum('status', ['pending', 'approved', 'completed', 'disapproved', 'archived'])
        ->default('pending');

        // Approval tracking
        $table->foreignId('approved_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
        $table->dateTime('approved_datetime')->nullable();

        // Completion tracking
        $table->foreignId('completed_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
        $table->dateTime('completed_datetime')->nullable();

        // Disapproval tracking
        $table->foreignId('disapproved_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
        $table->dateTime('disapproved_datetime')->nullable();

        // Archive tracking
        $table->foreignId('archived_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();
        $table->dateTime('archived_datetime')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_mass_productions');
    }
};
