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
        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->string('email');
            $table->string('telephone');
            $table->date('date_visite');
            $table->time('heure_visite');
            $table->text('message')->nullable();
            $table->string('statut')->default('en attente'); // 'en attente', 'confirmée', 'effectuée', 'annulée'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};
