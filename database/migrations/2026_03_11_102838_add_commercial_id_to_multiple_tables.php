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
        if (!Schema::hasColumn('agences', 'commercial_id')) {
            Schema::table('agences', function (Blueprint $table) {
                $table->string('commercial_id')->nullable();
                $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('proprietaires', 'commercial_id')) {
            Schema::table('proprietaires', function (Blueprint $table) {
                $table->string('commercial_id')->nullable();
                $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('biens', 'commercial_id')) {
            Schema::table('biens', function (Blueprint $table) {
                $table->string('commercial_id')->nullable();
                $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn('commercial_id');
        });

        Schema::table('proprietaires', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn('commercial_id');
        });

        Schema::table('biens', function (Blueprint $table) {
            $table->dropForeign(['commercial_id']);
            $table->dropColumn('commercial_id');
        });
    }
};
