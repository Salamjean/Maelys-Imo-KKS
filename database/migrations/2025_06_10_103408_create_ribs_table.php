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
        Schema::create('ribs', function (Blueprint $table) {
            $table->id();
            $table->string('rib')->unique();
            $table->string('banque');
            $table->string('path_rib_file');
            $table->string('proprietaire_id')->nullable();$table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->string('agence_id')->nullable();$table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ribs');
    }
};
