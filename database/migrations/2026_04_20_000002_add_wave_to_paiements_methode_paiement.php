<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE paiements MODIFY COLUMN methode_paiement ENUM('Espèces', 'Mobile Money', 'Virement Bancaire', 'Wave', 'Orange', 'Moov', 'MTN') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE paiements MODIFY COLUMN methode_paiement ENUM('Espèces', 'Mobile Money', 'Virement Bancaire') NOT NULL");
    }
};
