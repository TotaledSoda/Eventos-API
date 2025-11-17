<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   // database/seeders/DatabaseSeeder.php

public function run(): void
{
    $this->call(RolesAndPermissionsSeeder::class);

    // Crear super admin inicial
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'admin@app-eventos.test'],
        [
            'name'     => 'Super Admin',
            'password' => 'password', // se hashea por el cast
        ]
    );

    $user->assignRole('super-admin');
}

}
