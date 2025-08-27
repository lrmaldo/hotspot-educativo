<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trivias', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->enum('correct_option', ['A','B','C','D']);
            $table->boolean('active')->default(true); // Permite rotar preguntas
            $table->date('valid_on')->nullable(); // Opcional: fecha para controlar pregunta diaria
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trivias');
    }
};
