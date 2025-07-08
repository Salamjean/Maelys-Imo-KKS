<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationFieldsToPaymentPartnersTable extends Migration
{
    public function up()
    {
        Schema::table('payment_partners', function (Blueprint $table) {
            $table->string('verification_code', 6)->nullable()->after('beneficiaire_email');
            $table->string('code_valide_par')->nullable()->after('statut');
            $table->timestamp('date_validation')->nullable()->after('code_valide_par');
        });
    }

    public function down()
    {
        Schema::table('payment_partners', function (Blueprint $table) {
            $table->dropColumn([
                'verification_code',
                'code_valide_par',
                'date_validation'
            ]);
        });
    }
}