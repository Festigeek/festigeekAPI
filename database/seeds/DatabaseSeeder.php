<?php

use App\Address;
use App\Event;
use App\Order;
use App\Product;
use App\ProductType;
use App\Role;
use App\Permission;
use App\Team;
use App\User;
use App\PaymentType;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


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

        //////////////////
        // AUTHENTICATION
        //////////////////

        // Countries

        DB::unprepared(DB::raw(file_get_contents('database/scripts/data/countries.sql')));

        // Roles

        $admin = new Role();
        $admin->name         = 'admin';
        $admin->display_name = 'Admin'; // optional
        $admin->description  = 'Admins have all permissions on the app.'; // optional
        $admin->save();

        // Permissions

//        $editUser = new Permission();
//        $editUser->name         = 'edit-user';
//        $editUser->display_name = 'Edit Users'; // optional
//        $editUser->description  = 'Edit existing users'; // optional
//        $editUser->save();
//
//        $admin->attachPermission($editUser);

        // Users

        $adminUser = User::create(['username' => 'Admin', 'email' => 'admin@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04']);
        $adminUser->attachRole($admin);

        User::create(['username' => 'User', 'email' => 'user@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1998-08-27']);
        User::create(['username' => 'Drupal', 'email' => 'drupal@festigeek.ch', 'drupal_password' => '$S$DBJ/pPIJxOOl6qX7Cd09KtwzeHo75xYw.n3nVPiz3g8wcjdNAUO1', 'activated' => 0, 'birthdate' => '2000-01-01']);

        // Adresses

        Address::create(['user_id' => '1', 'country_id' => '229',  'street' => 'Route de Cheseaux 1',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);

        //////////////////
        //   E-COMMERCE
        //////////////////

        // Product_types

        ProductType::create(['name' => 'Inscriptions']);
        ProductType::create(['name' => 'Repas']);
        ProductType::create(['name' => 'Dons']);

        // Events

        Event::create(['name' => 'LAN 2017',
            'begins_at' => Carbon::create(2017, 05, 26, 20)->toDateTimeString(),
            'ends_at' => Carbon::create(2017, 05, 28, 18)->toDateTimeString()]);

        // Products

        $p1 = Product::create(['name' => 'League Of Legend', 'description' => 'Inscription LoL 2017.', 'price' => '20.00','quantity_max' => '80', 'sold' => '0', 'event_id' => 1, 'product_type_id' => '1']);
        $p2 = Product::create(['name' => 'Overwatch', 'description' => 'Inscription Overwatch 2017.', 'price' => '20.00', 'quantity_max' => '48', 'sold' => '0', 'event_id' => 1, 'product_type_id' => '1']);
        $p3 = Product::create(['name' => 'Counter-Strike: GO', 'description' => 'Inscription CS:GO 2017.', 'price' => '20.00', 'quantity_max' => '40', 'sold' => '0', 'event_id' => 1, 'product_type_id' => '1']);
        $p4 = Product::create(['name' => 'Animations', 'description' => 'Place joueur LAN 2017.', 'price' => '20.00', 'quantity_max' => '6', 'sold' => '0', 'event_id' => 1, 'product_type_id' => '1']);
        $p5 = Product::create(['name' => 'Burger', 'description' => "Bon d'achat pour un Burger Festigeek !", 'price' => '13.00', 'product_type_id' => '2']);
        $p6 = Product::create(['name' => 'Petit-déjeuner', 'description' => "Bon d'achat pour un petit-déjeuner.", 'price' => '5.00', 'product_type_id' => '2']);

        Product::create(['name' => 'Donation', 'description' => 'Donation pour l\'association.', 'price' => '0.00', 'product_type_id' => '3']);

        //Payment types

        PaymentType::create(['name' => 'PayPal']);
        PaymentType::create(['name' =>'Bank Transfer']);

        // Orders

        Order::create(['state' => '1', 'user_id' => '1', 'event_id' => '1', 'payment_type_id' => '1'])->products()->sync([$p1->id, $p5->id => ['amount' => '2']]);
        Order::create(['state' => '0', 'user_id' => '2', 'event_id' => '1', 'payment_type_id' => '2'])->products()->sync([$p2->id, $p5->id, $p6->id]);

//        Order::create(['state' => '1', 'user_id' => '1'])->products()->sync([$p1->id, $p5->id => ['amount' => '2']]);
//        Order::create(['state' => '0', 'user_id' => '2'])->products()->sync([$p2->id, $p5->id, $p6->id]);

        // Teams

        Team::create(['name' => "RageQuit"])->users()->sync([$adminUser->id => ['captain' => true, 'order_id' => '1']]);


        //////////////////
        //     CMS
        //////////////////

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
