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
        Schema::create('locataires', function (Blueprint $table) {
            $table->id();
            $table->string('code_id')->unique(); // Code d'identification unique
            $table->string('name');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('contact')->unique();
            $table->string('piece');
            $table->string('adresse');
            $table->string('profession');
            $table->string('attestation')->nullable();
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('status')->default('Actif'); // 'Inactif' or 'Actif'
            $table->string('motif')->nullable(); // Motif de l'inactivité
            $table->string('contrat');
            $table->string('agence_id')->nullable();$table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
            $table->foreignId('bien_id')->nullable()->constrained('biens')->onDelete('set null');
            $table->string('proprietaire_id')->nullable();$table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->foreignId('comptable_id')->nullable()->constrained('comptables')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locataires');
    }
};
