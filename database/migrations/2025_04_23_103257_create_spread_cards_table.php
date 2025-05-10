<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spread_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spread_id')->constrained()->onDelete('cascade');
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->timestamps();
            
            $table->index('spread_id');
            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spread_cards');
    }
};
