<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserOrderController extends Controller
{

  /**
   * @param Request $request
   * @param String $id
   */
  public function getOrders(Request $request, $id){
    $order = User::find($id)->orders()->get();
    return response()->json($order);
  }

}
