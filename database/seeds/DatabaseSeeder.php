<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\User;
use App\Address;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserTableSeeder::class);

        //Delete users and create an activated admin user with password "1234"
        Model::unguard();

        DB::unprepared(Storage::disk('local')->get('data/countries.sql'));

        DB::table('user_has_roles')->delete();
        DB::table('roles')->delete();
        $role = Role::create(['name' => 'admin']);

        DB::table('users')->delete();
        $adminUser = User::create(['username' => 'Admin', 'email' => 'admin@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04']);
        $adminUser->assignRole('admin');

        User::create(['username' => 'User', 'email' => 'user@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1998-08-27']);
        User::create(['username' => 'Drupal', 'email' => 'drupal@festigeek.ch', 'drupal_password' => '$S$DBJ/pPIJxOOl6qX7Cd09KtwzeHo75xYw.n3nVPiz3g8wcjdNAUO1', 'activated' => 0, 'birthdate' => '2000-01-01']);

        Address::create(['user_id' => '1', 'country_id' => '229',  'street' => 'Route de Cheseaux 1',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);




        // Create products
        // DB::table('products')->delete();
        // Product::create([
        //     'name' => 'Menu burger 1',
        //     'description' => 'Burger (Cheddar, salade mesclun, sauce à discretion), frites et boisson',
        //     'price' => '13'
        // ]);
        // Product::create([
        //     'name' => 'Menu burger 1',
        //     'description' => 'Burger (Fromage de chèvre, chorizo, miel, oignons caramélisés, salade mesclun, sauce à discretion), frites et boisson',
        //     'price' => '13'
        // ]);
        // Product::create([
        //     'name' => 'Menu burger 2',
        //     'description' => 'Burger (Cheddar, bacon, oignons caramélisés, guacamole maison, salade mesclun, sauce à discretion), frites et boisson',
        //     'price' => '13'
        // ]);
        // Product::create([
        //     'name' => 'Déjeuner',
        //     'description' => 'brunch composé de tartines (pain tresse), confitures, lait, chocolat en poudre, jus d’orange et café soluble',
        //     'price' => '5'
        // ]);

        // Add event
        // DB::table('events')->delete();
        // Event::create([
        //     'date' => '2016-05-06 16:00:00',
        //     'date_end' => '2016-05-08 18:30:00',
        //     'name' => 'LAN 2016',
        //     'description' => 'LAN Festigeek, édition 2016'
        // ]);

        // Add countries
        DB::unprepared(Storage::disk('local')->get('data/countries.sql'));

        Model::reguard();
    }
}
