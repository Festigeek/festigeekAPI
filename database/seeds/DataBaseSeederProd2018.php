<?php

use Illuminate\Database\Seeder;
use App\Event;
use App\Order;
use App\Product;
use App\ProductType;
use App\Configuration;
use App\Team;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DataBaseSeederProd2018 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //new event
        Event::create(['name' => 'LAN 2018',
            'begins_at' => Carbon::create(2018, 05, 26, 20)->toDateTimeString(),
            'ends_at' => Carbon::create(2018, 05, 28, 18)->toDateTimeString()]);
        //free burger config
        Configuration::create(['name' => 'timestamp-winner-2018', 'value' => time()]);//TODO time jusquau 7 mai
        //products
        $p7 = Product::create(['name' => 'League Of Legend', 'description' => 'Inscription LoL 2018.', 'price' => '20.00','quantity_max' => '84', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
        $p8 = Product::create(['name' => 'Overwatch', 'description' => 'Inscription Overwatch 2018.', 'price' => '20.00', 'quantity_max' => '48', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
        $p9 = Product::create(['name' => 'Counter-Strike: GO', 'description' => 'Inscription CS:GO 2018.', 'price' => '20.00', 'quantity_max' => '42', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
        $p10 = Product::create(['name' => 'Hearthstone', 'description' => 'Place joueur Hearthstone  2018.', 'price' => '20.00', 'quantity_max' => '12', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>0]);

        $p10 = Product::create(['name' => 'Animations', 'description' => 'Place joueur LAN 2018.', 'price' => '20.00', 'quantity_max' => '50', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>0]);
        $p11 = Product::create(['name' => 'Burger', 'description' => "Bon d'achat pour un Burger Festigeek !", 'price' => '14.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);
        $p12 = Product::create(['name' => 'Petit-déjeuner', 'description' => "Bon d'achat pour un petit-déjeuner.", 'price' => '5.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);
        Product::create(['name' => 'Burger Gratuit', 'description' => "Bon pour un Burger Festigeek gratuit.", 'price' => '0.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);

    }
}
