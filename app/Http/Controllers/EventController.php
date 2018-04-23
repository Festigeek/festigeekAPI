<?php

namespace App\Http\Controllers;

use App\Event;
use App\Order;
use App\Team;

use Illuminate\Http\Request;

class EventController extends Controller
{
    // TODO: make this dynamic
    private const FREE_PLAYERS = 13;

    /**
     * Get subcribed teams
     *
     * @param Request $request
     * @param String $id
     * @param mixed $game (product_id)
     */
    public function teams(Request $request, $id) {
        $event = Event::find($id);

        if(is_null($event))
            return response()->json(['error' => 'Event not found.'], 404);

        $game = ($request->filled('game')) ? $request->get('game') : null;

        if(!is_null($game)) {
            $orders = $event->orders()->where('event_id', $event->id)->whereHas('products', function($query) use($game) {
                return $query->where('product_id', $game);
            })->get();
        }
        else {
            $orders = $event->orders()->where('event_id', $event->id)->get();
        }

        $usersWithNoTeam = collect();
        $teams = $orders->map(function($order) use ($usersWithNoTeam) {
            if(is_null($order->team))
                $usersWithNoTeam->push($order->user);
            return $order->team;
        })->unique('id')->filter()->values();

        if($usersWithNoTeam->isNotEmpty()) {
            $noFriendsTeam["name"] = "Forever Alone";
            $noFriendsTeam["alias"] = "foreveralone";
            $noFriendsTeam["users"] = $usersWithNoTeam->map(function($user) {
                $u = collect($user->toArray())->only(['username', 'gender']);
                $u["roaster"] = true;
                $u["captain"] = false;
                return $u;
            });
            $noFriendsTeam["game"] = self::FREE_PLAYERS;

            $teams->push($noFriendsTeam);
        }

        return response()->json($teams);
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
