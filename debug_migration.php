<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Checking Agences...\n";
    Schema::table('agences', function (Blueprint $table) {
        $table->string('commercial_id')->nullable();
        $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
    });
    echo "Agences OK\n";

    echo "Checking Proprietaires...\n";
    Schema::table('proprietaires', function (Blueprint $table) {
        $table->string('commercial_id')->nullable();
        $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
    });
    echo "Proprietaires OK\n";

    echo "Checking Biens...\n";
    Schema::table('biens', function (Blueprint $table) {
        $table->string('commercial_id')->nullable();
        $table->foreign('commercial_id')->references('code_id')->on('commercials')->onDelete('set null');
    });
    echo "Biens OK\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
