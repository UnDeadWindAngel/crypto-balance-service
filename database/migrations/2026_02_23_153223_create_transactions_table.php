<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('currency_id')->constrained();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount_total', 20, 8);
            $table->decimal('amount_principal', 20, 8);
            $table->decimal('amount_fee_network', 20, 8)->default(0);
            $table->decimal('amount_fee_service', 20, 8)->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('external_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('account_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
