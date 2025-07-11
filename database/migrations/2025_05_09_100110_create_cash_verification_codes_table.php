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
        Schema::create('cash_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locataire_id')->constrained()->onDelete('cascade');
            $table->foreignId('paiement_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code', 6);
            $table->integer('nombre_mois')->default(1);
            $table->string('mois_couverts')->nullable();
            $table->decimal('montant_total', 10, 2)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_verification_codes');
    }
};
