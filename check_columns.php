<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Agences:\n";
print_r(Schema::getColumnListing('agences'));

echo "Proprietaires:\n";
print_r(Schema::getColumnListing('proprietaires'));

echo "Biens:\n";
print_r(Schema::getColumnListing('biens'));
