<?php

namespace App\Http\Controllers;
include("Auth/drupal_password.inc");

use DB;
use Crypt;
use Response;
use JWTAuth;

use App\User;
use App\Mail\RegisterMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['authenticate', 'register', 'test']]);
        $this->middleware('auth.activated', ['except' => ['authenticate', 'register', 'activate', 'test']]);
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
                return response()->json(['error' => 'user_not_found'], 401);
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
                    return Response::json(['drupal_account' => true]);
                }
            }
            else {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        }

        $response = compact('token');

        return response()->json($response);
    }

    /**
     * create a new user and send a activation mail.
     *
     * @param  Request  $request
     * @return Response
     */
    public function activate(Request $request) {
        $registration_token = $request->only('token');

        try {
            $user = User::where('registration_token', $registration_token)->findOrFail();
        }
        catch (Exception $e) {
            return response()->json(['error' => 'user_not_found'], 401);
        }

        $user->activated = true;
        $user->save();

        return response()->json(['success' => 'user_activated'], 200);
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
        catch (Exception $e) {
            return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
        }

        Mail::to($newuser->email, $newuser->username)->send(new RegisterMail($newuser));

        $token = JWTAuth::fromUser($newuser);
        return Response::json(compact('token'));
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
        //TODO: Check access permission
        $user = User::findOrFail($id);
        return response()->json(compact('user'));
    }

    public function test(Request $request) {
        // Test function
    }
}
