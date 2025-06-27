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
        Schema::create('proprietaires', function (Blueprint $table) {
            $table->id();
            $table->string('code_id')->unique();
            $table->string('name');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('commune');
            $table->string('contact');
            $table->string('choix_paiement');
            $table->string('rib')->nullable();
            $table->string('pourcentage')->nullable();
            $table->string('profil_image')->nullable();
            $table->string('contrat')->nullable();
            $table->string('gestion')->nullable();
            $table->string('diaspora')->nullable();
            $table->dateTime('last_balance_update')->nullable();
            $table->string('agence_id')->nullable(); // ou $table->foreignId('agence_id') si code_id est un entier
            $table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proprietaires');
    }
};
