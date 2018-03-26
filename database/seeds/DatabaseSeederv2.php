<?php

use App\Event;
use App\Order;
use App\Product;
use App\ProductType;
use App\Role;
use App\Configuration;
use App\Team;
use App\User;
use App\PaymentType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


class DatabaseSeederv2 extends Seeder
{
  public function run()
  {
  //users
  $returningUser = User::create(['username' => 'ReturningUser', 'email' => 'returning@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04', 'country_id' => '229',  'street' => 'Route de Cheseaux 69',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);
  $newUser = User::create(['username' => 'NewUser', 'email' => 'newuser@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04', 'country_id' => '229',  'street' => 'Route de Cheseaux 98',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);
  $returningUserWithOrder =  User::create(['username' => 'ReturningUserWithOrder', 'email' => 'returningorder@festigeek.ch', 'password' => '1234', 'activated' => 1, 'birthdate' => '1934-09-04', 'country_id' => '229',  'street' => 'Route de Cheseaux 69',  'npa' => '1400',  'city' => 'Yverdon-Les-Bains']);

  //products
  //2018 event id
  $p7 = Product::create(['name' => 'League Of Legend', 'description' => 'Inscription LoL 2017.', 'price' => '20.00','quantity_max' => '80', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
  $p8 = Product::create(['name' => 'Overwatch', 'description' => 'Inscription Overwatch 2017.', 'price' => '20.00', 'quantity_max' => '48', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
  $p9 = Product::create(['name' => 'Counter-Strike: GO', 'description' => 'Inscription CS:GO 2017.', 'price' => '20.00', 'quantity_max' => '40', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>1]);
  $p10 = Product::create(['name' => 'Animations', 'description' => 'Place joueur LAN 2017.', 'price' => '20.00', 'quantity_max' => '6', 'sold' => '0', 'event_id' => 2, 'product_type_id' => 1, 'need_team'=>0]);
  $p11 = Product::create(['name' => 'Burger', 'description' => "Bon d'achat pour un Burger Festigeek !", 'price' => '14.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);
  $p12 = Product::create(['name' => 'Petit-déjeuner', 'description' => "Bon d'achat pour un petit-déjeuner.", 'price' => '5.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);
  Product::create(['name' => 'Burger Gratuit', 'description' => "Bon pour un Burger Festigeek gratuit.", 'price' => '0.00', 'product_type_id' => 2, 'sold' => '0', 'event_id' => 2, 'need_team'=>0]);

  Product::create(['name' => 'Donation', 'description' => 'Donation pour l\'association.', 'price' => '0.00', 'product_type_id' => '3']);

  //orders
  $oldOrder = Order::create(['state' => '1', 'user_id' => $returningUser->id, 'event_id' => '1', 'payment_type_id' => '1'])->products()->sync([1, 2 => ['amount' => '2']]);
  $newOrderReturning = Order::create(['state' => '1', 'user_id' => $returningUserWithOrder->id, 'event_id' => '2', 'payment_type_id' => '1'])->products()->sync([$p7->id, $p12->id => ['amount' => '2']]);

//TODO test if we can use old team.. ? ATTENTION is it possible to only view current event_id (not here, in controller)
  //Team
  Team::create(['name' => "old Team"])->users()->sync([2 => ['captain' => true, 'order_id' => $oldOrder]]);
  Team::create(['name' => "Testing Baby"])->users()->sync([2 => ['captain' => true, 'order_id' => '2']]);
}

}
