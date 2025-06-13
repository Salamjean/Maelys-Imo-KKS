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
            $table->foreignId('agent_id')->constrained('comptables')->onDelete('cascade');
            $table->foreignId('comptable_id')->constrained('comptables')->onDelete('cascade');
            $table->decimal('montant_verse', 10, 2); // Montant que l'agent donne
            $table->decimal('montant_percu', 10, 2); // Total des paiements perçus par l'agent
            $table->decimal('reste_a_verser', 10, 2); // Reste après ce versement
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
