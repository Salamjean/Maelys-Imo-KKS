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
        Schema::create('etat_lieus', function (Blueprint $table) {
            $table->id();
            // Informations generales sur l'état des lieux
            $table->string('adresse_bien')->nullable();
            $table->string('type_bien')->nullable();
            $table->string('lot')->nullable();
            $table->string('date_etat')->nullable();
            $table->string('nature_etat')->nullable();
            $table->string('nom_locataire')->nullable();
            $table->string('nom_proprietaire')->nullable();
            $table->string('presence_partie')->nullable();
            $table->string('etat_entre')->nullable();
            $table->string('etat_sorti')->nullable();

            //Relevés des compteurs 
            $table->string('type_compteur')->nullable();
            $table->string('numero_compteur')->nullable();
            $table->string('releve_entre')->nullable();
            $table->string('releve_sorti')->nullable();

            //etats des lieux par piece
            $table->string('sol')->nullable();
            $table->string('murs')->nullable();
            $table->string('plafond')->nullable();
            $table->string('porte_entre')->nullable();
            $table->string('interrupteur')->nullable();
            $table->string('eclairage')->nullable();
            $table->string('remarque')->nullable();

            //Clés étrangères 
            $table->string('agence_id')->nullable();$table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
            $table->string('locataire_id')->nullable();$table->foreign('locataire_id')->references('code_id')->on('locataires')->onDelete('cascade');
            $table->string('proprietaire_id')->nullable();$table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etat_lieus');
    }
};
