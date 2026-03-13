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
        Schema::table('proprietaires', function (Blueprint $table) {
            $table->string('cni')->nullable()->change();
            $table->string('rib')->nullable()->change();
            $table->string('choix_paiement')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proprietaires', function (Blueprint $table) {
            $table->string('cni')->nullable(false)->change();
            $table->string('rib')->nullable(false)->change();
            $table->string('choix_paiement')->nullable(false)->change();
        });
    }
};
