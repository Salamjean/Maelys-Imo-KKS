<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriqueBiensTable extends Migration
{
    public function up()
    {
        Schema::create('historique_biens', function (Blueprint $table) {
            $table->id();
            // Infos Agence
            $table->string('agence_code');
            
            // Infos Propriétaire (Snapshot au moment de la suppression)
            $table->string('proprietaire_code');
            $table->string('proprietaire_nom_complet');
            
            // Infos Bien
            $table->string('bien_type');
            $table->string('bien_commune');
            $table->decimal('bien_prix', 12, 2);
            
            // Infos Locataire (S'il y en avait un)
            $table->string('locataire_nom_complet')->nullable();
            $table->string('locataire_contact')->nullable();
            
            $table->date('date_suppression');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('historique_biens');
    }
}