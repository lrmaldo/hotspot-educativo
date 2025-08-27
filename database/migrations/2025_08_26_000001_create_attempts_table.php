<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            // Forzamos la tabla correcta 'trivias' (plural) porque la inferencia de 'trivia_id' -> 'trivia' es incorrecta
            $table->foreignId('trivia_id')->constrained('trivias')->cascadeOnDelete();
            $table->string('identifier')->index(); // IP o MAC del dispositivo
            $table->enum('selected_option', ['A','B','C','D']);
            $table->boolean('is_correct');
            $table->string('mikrotik_username')->nullable();
            $table->string('mikrotik_password')->nullable();
            $table->unsignedInteger('granted_minutes')->default(0);
            $table->timestamps();

            $table->index(['identifier','created_at']);
        });
    }

    public function down(): void
    {
    Schema::dropIfExists('attempts');
    }
};
