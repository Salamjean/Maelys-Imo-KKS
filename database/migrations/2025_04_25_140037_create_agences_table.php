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
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('code_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('commune');
            $table->string('password');
            $table->string('contact');
            $table->string('adresse');
            $table->string('rccm');
            $table->string('rccm_file');
            $table->string('dfe');
            $table->string('dfe_file');
            $table->string('rib')->nullable();
            $table->string('profile_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
