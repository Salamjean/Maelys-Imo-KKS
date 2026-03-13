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
        Schema::table('commercials', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('profile_image');
            $table->string('password_reset_otp')->nullable()->after('fcm_token');
            $table->string('password_reset_token', 80)->nullable()->after('password_reset_otp');
            $table->timestamp('password_reset_expires')->nullable()->after('password_reset_token');
            $table->integer('otp_attempts')->default(0)->after('password_reset_expires');
            $table->timestamp('otp_verified_at')->nullable()->after('otp_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commercials', function (Blueprint $table) {
            $table->dropColumn([
                'fcm_token',
                'password_reset_otp',
                'password_reset_token',
                'password_reset_expires',
                'otp_attempts',
                'otp_verified_at'
            ]);
        });
    }
};
