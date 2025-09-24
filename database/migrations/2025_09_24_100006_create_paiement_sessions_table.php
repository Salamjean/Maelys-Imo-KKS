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
        Schema::create('paiement_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('locataire_id')->constrained();
            $table->foreignId('bien_id')->constrained();
            $table->decimal('montant', 10, 2);
            $table->string('mois_couvert');
            $table->json('metadata'); // Stocke toutes les données supplémentaires
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement_sessions');
    }
};
