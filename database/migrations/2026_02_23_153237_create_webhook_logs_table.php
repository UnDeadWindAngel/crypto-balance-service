<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event')->nullable(); // тип события, например, deposit
            $table->json('payload');
            $table->string('external_id')->nullable()->index();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->integer('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
