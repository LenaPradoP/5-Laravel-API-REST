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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['major_arcana', 'minor_arcana']);
            $table->integer('number');
            $table->string('name');
            $table->enum('suit', ['cups', 'swords', 'wands', 'pentacles'])->nullable();
            $table->enum('element', ['air', 'water', 'fire', 'earth']);
            $table->text('meaning');
            $table->timestamps();

            // Índices para mejor rendimiento
            $table->index('type');
            $table->index('suit');
            $table->unique(['type', 'number', 'suit']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};