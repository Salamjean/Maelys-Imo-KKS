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
        Schema::create('payment_partners', function (Blueprint $table) {
            $table->id();
            $table->string('proprietaire_id');
            $table->string('agence_id');
            $table->string('mode_paiement');
            $table->decimal('montant', 10, 2);
            $table->boolean('est_proprietaire')->default(false);
            $table->string('rib')->nullable();
            $table->string('statut')->default('payé');
            $table->string('fichier_paiement')->nullable();
            $table->string('beneficiaire_nom')->nullable();
            $table->string('beneficiaire_prenom')->nullable();
            $table->string('beneficiaire_contact')->nullable();
            $table->string('beneficiaire_email')->nullable();
            $table->string('numero_cni')->nullable();
            $table->timestamps();

            // Clés étrangères
            $table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_partners');
    }
};
