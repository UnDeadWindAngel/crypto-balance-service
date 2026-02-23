<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('balance', 20, 8)->default(0);
            $table->integer('version')->default(1);
            $table->timestamps();
            $table->unique(['user_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
