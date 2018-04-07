<?php

namespace App\Http\Controllers;

use App\Event;
use App\Order;
use App\Team;

use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get subcribed teams
     *
     * @param Request $request
     * @param String $id
     * @param mixed $game (product_id)
     */
    public function teams(Request $request, $id){
        $event = Event::find($id);

        if(is_null($event))
            return response()->json(['error' => 'Event not found.'], 404);

        $game = ($request->filled('game')) ? $request->get('game') : null;
        $orders = (!is_null($game)) ? Order::where('event_id', $id)->has('products', $game)->get() : Order::where('event_id', $id)->get();
        $teams = $event->teams();

        $filteredTeams = $teams->filter(function($team) use($orders) {
            return $orders->pluck('team')->contains('id', $team->id);
        })->all();

        return response()->json($filteredTeams);
    }

    public function teamFromCode(Request $request, $event_id, $team_code) {
        $team = Team::where('code', $team_code)->first();

        if(is_null($team))
            return response()->json(['error' => 'Team not found.'], 404);
        else
            return response()->json(['name' => $team->name]);
    }

    public function updateTeam(Request $request, $event_id, $team_id) {
        if(!$request->has('captain'))
            return response()->json(['error' => 'Missing parameters.'], 422);

        $email = $request->get('captain');
        $event = Event::find($event_id);
        if(is_null($event))
            return response()->json(['error' => 'Event not found.'], 404);

        $team = Team::find($team_id);
        if(is_null($team))
            return response()->json(['error' => 'Team not found.'], 404);

        $newCaptain = $team->users()->where('email', $email)->first();
        if(is_null($newCaptain))
            return response()->json(['error' => 'Request was well-formed but was unable to be followed due to content errors'], 422);

        if(!$this->isAdminOrOwner($team->captain->id)) {
            return response()->json(['error' => 'Permission denied'], 401);
        }
        
        $team->users()->updateExistingPivot($team->captain->id, ['captain' => false]);
        $team->users()->updateExistingPivot($newCaptain->id, ['captain' => true]);

        /*
        if(!$request->has('users') || !$request->has('captain'))
            return response()->json(['error' => 'Missing parameters.'], 422);

        $event = Event::find($event_id);
        $team = Team::find($team_id);
        $users_ids = collect($request->get('users'));
        $nb_users = $users_ids->count();
        $captain_id = $request->get('captain');

        if(is_null($event))
            return response()->json(['error' => 'Event not found.'], 404);

        if(is_null($team))
            return response()->json(['error' => 'Team not found.'], 404);

        if(!$team->hasUser($captain_id) || $team->users()->get()->count() < $nb_users || $nb_users === 0)
            return response()->json(['error' => 'Request was well-formed but was unable to be followed due to content errors'], 422);

        if(!$this->isAdminOrOwner($team->captain->id)) {
            return response()->json(['error' => 'Invalid Credentials.'], 401);
        }

        // If only one user in team, automatically promoted as captain
        if($nb_users === 1)
            $captain_id = $users_ids->first();

        // Can only REMOVE users. Adding users would result with no related order intermediate table
        $users = $users_ids->mapWithKeys(function($item) use($team, $captain_id) {
            $order_id = $team->users()->where('user_id', '=', $item)->first()->pivot->orderId;
            return [$item => [
                'captain' => ($item === $captain_id) ? 1 : 0,
                'order_id' => $team->orders()->where('orders.user_id', '=', $item)->first()->id
            ]];
        });

        $team->users()->detach();
        $team->users()->attach($users->all());
        $team->save();
        */

        return response()->json(['success' => 'Team updated'], 200);
    }

    /*
    public function team(Request $request, $event_id, $team_id){

        $game = ($request->filled('game')) ? $request->get('game') : null;
        $strict = ($request->filled('strict')) ? $request->get('strict') : null;
        $orders = Order::where('event_id', $event_id)->get();
        $team = $orders[0]->team;
        if($team->id != $team_id){
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // $teams = $orders->filter(function($value) use ($game) {
        //     return (!is_null($game)) ? $value->products()->where('product_id', $game)->count() > 0 : true;
        // })->map(function($order) use ($game, $strict) {
        //     $teams = $order->team()->get();
        //     return $teams;
        // })->flatten()->filter(function($value) {
        //     return !is_null($value);
        // })->unique('id')->sortBy('name')->values();

        return response()->json($team);
    }
    */

    /**
     * @param Request $request
     * @param $id event id
     */
    public function products(Request $request, $id){
        $products = Event::find($id)->products()->get();
        return response()->json($products);
    }

    /**
     * @param Request $request
     * @param $id event id
     */
    public function current(){
        $currentEvent = Event::whereDate('ends_at', '>=', date('Y-m-d'))->orderBy('begins_at', 'asc')->first();
        return response()->json($currentEvent);
    }
}
