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
        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('comptables')->onDelete('cascade'); // L'agent qui verse
            $table->foreignId('comptable_id')->constrained('comptables')->onDelete('cascade'); // Le comptable qui reçoit
            $table->decimal('montant', 10, 2); // Ex: 1000000.00
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versements');
    }
};
