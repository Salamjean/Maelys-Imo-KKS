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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->string('proprietaire_id')->nullable();$table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade'); // ou $table->foreignId('proprietaire_id') si vous préférez
            $table->string('agence_id')->nullable();$table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade'); // ou $table->foreignId('proprietaire_id') si vous préférez
            $table->date('date_abonnement'); // date à laquelle l'abonnement a été souscrit
            $table->date('date_debut'); // date de début de l'abonnement
            $table->date('date_fin'); // date de fin de l'abonnement
            $table->string('mois_abonne'); // le mois pour lequel l'abonnement est valide (peut être formaté comme "01-2023")
            $table->decimal('montant', 10, 2); // montant de l'abonnement
            $table->decimal('montant_actuel', 10, 2)->nullable(); // montant de l'abonnement
            $table->string('statut')->default('actif'); // actif, expiré, annulé, etc.
            $table->string('mode_paiement'); // mode de paiement utilisé
            $table->string('reference_paiement')->nullable(); // référence du paiement
            $table->text('notes')->nullable(); // notes supplémentaires
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
