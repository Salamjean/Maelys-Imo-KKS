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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant', 10, 2);
            $table->date('date_paiement');
            $table->string('mois_couvert'); // Format "2023-11"
            $table->enum('methode_paiement', ['Espèces', 'Mobile Money']);
            $table->string('verif_espece')->nullable(); // Code pour vérification manuelle
            $table->string('transaction_id')->nullable(); // Pour CinetPay
            $table->enum('statut', ['En attente', 'payé', 'échoué'])->default('En attente');
            
            // Clés étrangères
             $table->string('locataire_id')->nullable();$table->foreign('locataire_id')->references('code_id')->on('locataires')->onDelete('cascade');
            $table->foreignId('bien_id')->constrained()->onDelete('cascade');
            $table->string('comptable_id')->nullable();$table->foreign('comptable_id')->references('code_id')->on('comptables')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
