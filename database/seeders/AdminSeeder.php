<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Locataire;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Admin::create([
            'name' => 'Maelys-Imo',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('azertyui'),
        ]);
    }
}
