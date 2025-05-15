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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locataire_id')->constrained('locataires')->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('loyer_mensuel', 10, 2);
            $table->decimal('caution', 10, 2);
            $table->decimal('avance', 10, 2);
            $table->string('fichier_path'); // Chemin vers le fichier PDF
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
