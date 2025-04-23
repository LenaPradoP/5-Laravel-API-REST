<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spreads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->constrained()->onDelete('cascade');
            $table->enum('spread_type', ['first', 'second'])->default('first');
            $table->timestamp('creation_date')->useCurrent();
            $table->timestamps();
            
            $table->index('deck_id');
            $table->index('spread_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spreads');
    }
};
