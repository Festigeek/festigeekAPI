<?php

namespace App\Http\Controllers;
include("Auth/drupal_password.inc");

use DB;
use Crypt;
use Response;
use JWTAuth;
use Validator;

use App\Address;
use App\User;
use App\Mail\RegisterMail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('jwt.auth', ['except' => ['authenticate', 'register', 'activation', 'test']]);
        $this->middleware('role:admin', ['only' => ['index']]);
    }

    public function authenticate(Request $request) {
        $credentials = $request->only('email', 'password');
//        $token = false;
//        $response = [];

        if (!$token = JWTAuth::attempt($credentials)) {
            // We do not use the ORM because the property 'drupal_password' is hidden, and we need it.
            $drupal_user = DB::table('users')->where('email', $credentials['email'])->where('activated', false)->first();

            if(is_null($drupal_user)) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if(user_check_password($credentials['password'], $drupal_user)) {
                if($request->has('newPassword')) {
                    // Update account
                    DB::table('users')
                        ->where('id', $drupal_user->id)
                        ->update(['password' => Hash::make($request->input('newPassword')), 'activated' => 1]);

                    // Re-create JWToken
                    $credentials['password'] = $request->input('newPassword');
                    $token = JWTAuth::attempt($credentials);
                }
                else {
                    return Response::json(['success' => 'drupal_account']);
                }
            }
            else {
                return response()->json(['error' => 'Invalid Credentials.'], 401);
            }
        }

        if(!JWTAuth::user()->activated) {
            return response()->json(['error' => 'Inactive Account.'], 401);
        }

//        $response = compact('token');

        return response()->json(['success' => 'Authenticated.', 'token' => $token]);
    }

    /**
     * create a new user and send a activation mail.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request) {
        try {
            $newuser = User::create($request->all());
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'User already exists.'], 409);
        }

        Mail::to($newuser->email, $newuser->username)->send(new RegisterMail($newuser));

        //$token = JWTAuth::fromUser($newuser);
        //return response()->json(compact('token'));
        return response()->json(['success' => 'Account created.'], 200);
    }

    /**
     * Activate the user with a given activation token
     *
     * @param  Request  $request
     * @return Response
     */
    public function activation(Request $request) {
        if($request->has('activation_token')) {
            $registration_token = $request->get('activation_token');

            try {
                $user = User::where('registration_token', $registration_token)->firstOrFail();
            }
            catch (\Exception $e) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if($user->activated)
                return response()->json(['success' => 'User already activated'], 200);
            else {
                $user->activated = true;
                $user->save();
                return response()->json(['success' => 'User activated'], 200);
            }
        }
        else
            return response()->json(['error' => 'No registration token provided'], 422);
    }

    //TODO: new end-point to re-generate a new couple of registration token / e-mail

    /**
     * show all users.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request) {
        $users = User::all()->makeHidden(['QRCode']);
        return response()->json($users);
    }

    /**
     * show a user.
     *
     * @param  id id of the user to show
     * @return Response
     */
    public function show($id) {
//        $loggedUser = JWTAuth::user();

        if($this->isAdminOrOwner($id)) {
            $id = ($id === 'me') ? JWTAuth::user()->id : $id;
            try {
                $user = User::findOrFail($id);
                return response()->json($user);
            }
            catch (\Exception $e) {
                return response()->json(['error' => 'User not found'], 404);
            }
        }
        else abort(403);
//
//        if($id === 'me' || $loggedUser->id == $id) {
//            $user = User::find($loggedUser->id);
//            return response()->json($user);
//        }
//        else if($loggedUser->hasRole('admin')) {
//            try {
//                $user = User::findOrFail($id);
//                return response()->json($user);
//            }
//            catch (\Exception $e) {
//                return response()->json(['error' => 'User not found'], 404);
//            }
//        }
//        else abort(403);
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($this->isAdminOrOwner($id)) {
            try {
                $id = ($id === 'me') ? JWTAuth::user()->id : $id;
                $user = User::findOrFail($id);
            }
            catch (\Exception $e) {
                return response()->json(['error' => 'User not Found'], 404);
            }

            $inputs = $request->only(['gender',
                'firstname',
                'lastname',
                'country_id',
                'street',
                'street2',
                'npa',
                'city',
                'lol_account',
                'steamID64',
                'battleTag']);

            $validator = Validator::make($inputs, [
//                'username' => 'required|string|unique:users',
//                'email' => 'required|email|unique:users',
//                'password' => 'min:8',
//                'birthdate' => 'required|date|date_format:YYYY-mm-dd',

                'gender' => 'in:M,F',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'country_id' => 'required|numeric',
                'street' => 'required|string',
                'street2' => 'nullable|string',
                'npa' => 'required|string',
                'city' => 'required|string',

                'lol_account' => 'nullable|string',
                'steamID64' => 'nullable|numeric',
                'battleTag' => 'nullable|string'
            ]);

            if ($validator->fails())
                return response()->json(['error' => 'Validation error.', 'validation' => $validator], 400);

            $user->fill($inputs)->save();

            return response()->json($user);
        }
        else abort(403);
    }

    public function test(Request $request) {
        // Test function
    }
}
