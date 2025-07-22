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
        Schema::create('etat_lieu_sortis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('locataire_id')->constrained('locataires')->onDelete('cascade');
                $table->foreignId('bien_id')->constrained('biens')->onDelete('cascade');
                $table->string('type_bien')->nullable();
                $table->string('commune_bien')->nullable();
                $table->string('presence_partie')->nullable();
                $table->string('status_etat_entre')->nullable();
                $table->string('status_sorti')->nullable();

                // Parties communes
                $table->json('parties_communes')->nullable();
                
                // Chambres (stockées en JSON pour flexibilité)
                $table->json('chambres')->nullable();
                
                $table->string('nombre_cle')->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etat_lieu_sortis');
    }
};
