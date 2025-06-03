<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordResetColumnsToAgencesTable extends Migration
{
    public function up()
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->string('password_reset_token')->nullable()->after('password');
            $table->timestamp('password_reset_expires')->nullable()->after('password_reset_token');
        });
    }

    public function down()
    {
        Schema::table('agences', function (Blueprint $table) {
            $table->dropColumn(['password_reset_token', 'password_reset_expires']);
        });
    }
}