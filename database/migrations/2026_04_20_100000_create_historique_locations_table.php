<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locataire_id')->constrained('locataires')->onDelete('cascade');
            $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
            $table->string('agence_id')->nullable();
            $table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('set null');
            $table->string('proprietaire_id')->nullable();
            $table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('set null');
            $table->date('date_entree');
            $table->date('date_sortie')->nullable();
            $table->string('motif_sortie')->nullable();
            $table->foreignId('etat_lieu_entree_id')->nullable()->constrained('etat_lieus')->onDelete('set null');
            $table->foreignId('etat_lieu_sortie_id')->nullable()->constrained('etat_lieu_sortis')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_locations');
    }
};
