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
        Schema::create('reversements', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant', 10, 2);
            $table->string('reference')->unique();
            $table->date('date_reversement');
            $table->string('recu_paiement')->nullable();
            $table->enum('statut', ['En attente', 'Effectué', 'Échoué'])->default('En attente');
            $table->foreignId('rib_id')->constrained()->onDelete('cascade');
            $table->string('proprietaire_id')->nullable();
            $table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reversements');
    }
};
