<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reversements', function (Blueprint $table) {
            $table->enum('type_retrait', ['virement', 'mobile_money'])->default('virement')->after('statut');
            $table->string('reseau_mobile')->nullable()->after('type_retrait'); // Wave, Orange, Moov, MTN
            $table->string('numero_mobile')->nullable()->after('reseau_mobile');
            $table->unsignedBigInteger('rib_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reversements', function (Blueprint $table) {
            $table->dropColumn(['type_retrait', 'reseau_mobile', 'numero_mobile']);
        });
    }
};
