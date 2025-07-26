<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminPasswordUpdateSeeder extends Seeder
{
    public function run()
    {
        User::where('email', 'admin@gmail.com')->update([
            'password' => Hash::make('25kHZBv[9vpq')
        ]);
    }
}
