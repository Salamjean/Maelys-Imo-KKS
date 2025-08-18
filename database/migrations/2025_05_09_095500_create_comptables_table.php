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
        Schema::create('comptables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_id')->unique();
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('commune');
            $table->string('password');
            $table->string('contact');
            $table->string('date_naissance');
            $table->string('user_type');
            $table->string('profile_image')->nullable();
            $table->string('agence_id')->nullable();$table->foreign('agence_id')->references('code_id')->on('agences')->onDelete('cascade');
            $table->string('proprietaire_id')->nullable();$table->foreign('proprietaire_id')->references('code_id')->on('proprietaires')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptables');
    }
};
