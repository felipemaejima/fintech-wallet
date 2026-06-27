<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('transactions', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('wallet_id')
        ->constrained()
        ->cascadeOnDelete();
      $table->enum('type', ['credit', 'debit']);
      $table->decimal('amount', 15, 2);
      $table->decimal('balance_after', 15, 2);
      $table->enum('status', ['pending', 'completed', 'failed'])
        ->default('completed');
      $table->timestamps();

      $table->index(['wallet_id', 'created_at']);
      $table->index('type');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
