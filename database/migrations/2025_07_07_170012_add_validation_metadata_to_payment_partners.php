<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddValidationMetadataToPaymentPartners extends Migration
{
    public function up()
    {
        Schema::table('payment_partners', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_partners', 'code_valide_par')) {
                $table->string('code_valide_par')->nullable()->after('verification_code');
            }
            
            if (!Schema::hasColumn('payment_partners', 'date_validation')) {
                $table->timestamp('date_validation')->nullable()->after('code_valide_par');
            }
        });
    }

    public function down()
    {
        Schema::table('payment_partners', function (Blueprint $table) {
            $table->dropColumn(['code_valide_par', 'date_validation']);
        });
    }
}