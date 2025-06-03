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
        Schema::create('biens', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('utilisation');
            $table->string('description');
            $table->string('superficie');
            $table->string('nombre_de_chambres')->nullable();
            $table->string('nombre_de_toilettes')->nullable();
            $table->string('garage')->nullable();
            $table->string('avance')->nullable();
            $table->string('caution')->nullable();
            $table->string('frais')->nullable();
            $table->string('montant_total')->nullable();
            $table->string('prix');
            $table->string('commune');
            $table->decimal('montant_majore', 10, 2)->nullable();
            $table->string('date_fixe')->nullable();
            $table->string('image');
            $table->string('image1');
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();
            $table->string('image5')->nullable();
            $table->string('status')->default('Disponible');
            $table->foreignId('agence_id')->nullable()->constrained('agences')->onDelete('cascade');
            $table->foreignId('proprietaire_id')->nullable()->constrained('proprietaires')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biens');
    }
};
