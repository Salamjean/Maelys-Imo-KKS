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
        Schema::table('locataires', function (Blueprint $table) {
            $table->string('password_reset_otp', 6)->nullable()->after('password_reset_token');
            $table->integer('otp_attempts')->default(0)->after('password_reset_otp');
            $table->string('reset_access_token', 80)->nullable()->after('otp_attempts');
            $table->timestamp('reset_access_expires')->nullable()->after('reset_access_token');
            $table->timestamp('otp_verified_at')->nullable()->after('reset_access_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locataires', function (Blueprint $table) {
             $table->dropColumn([
                'password_reset_otp',
                'otp_attempts',
                'reset_access_token',
                'reset_access_expires',
                'otp_verified_at'
            ]);
        });
    }
};
