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

        // Countries

        DB::table('addresses')->delete();
        DB::table('countries')->delete();
        DB::unprepared(Storage::disk('local')->get('data/countries.sql'));

        //Roles

        DB::table('user_has_roles')->delete();
        DB::table('roles')->delete();
        $role = Role::create(['name' => 'admin']);

        // Users

        DB::table('users')->delete();
        $adminUser = User::create(['username' => 'Admin', 'email' => 'admin@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04']);
        $adminUser->assignRole('admin');

        User::create(['username' => 'User', 'email' => 'user@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1998-08-27']);
        User::create(['username' => 'Drupal', 'email' => 'drupal@festigeek.ch', 'drupal_password' => '$S$DBJ/pPIJxOOl6qX7Cd09KtwzeHo75xYw.n3nVPiz3g8wcjdNAUO1', 'activated' => 0, 'birthdate' => '2000-01-01']);

        // Adresses

        Address::create(['user_id' => '1', 'country_id' => '229',  'street' => 'Route de Cheseaux 1',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);

        // ContentsTypes

        // DB::table('contents_types')->delete();
        // ContentsType::create(['name' => 'Page']);
        // ContentsType::create(['name' => 'Article']);

        // DatasTypes

        // DB::table('datas_types')->delete();
        // DatasType::create(['name' => 'Texte Brut']);
        // DatasType::create(['name' => 'HTML']);

        // Datas

        // Data::create([]);

        // Contents

        // Content::create([]);

        // Links

        // $lien1 = Link::create(['display_text' => "L'association", 'content_id' => '1']);
        // $lien2 = Link::create(['display_text' => "Contact", 'url' => '/contact']);

        // Menus

        // Menu::create(['name' => 'Principal'])->sync([$lien1, $lien2]);


        Model::reguard();
    }
}
