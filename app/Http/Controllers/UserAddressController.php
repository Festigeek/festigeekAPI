<?php
//
//namespace App\Http\Controllers;
//
//use App\Address;
//use Illuminate\Http\Request;
//
//use App\Http\Requests;
//use Tymon\JWTAuth\JWTAuth;
//
//class UserAddressController extends Controller
//{
//    public function __construct()
//    {
//        // Apply the jwt.auth middleware to all methods in this controller
//        $this->middleware('jwt.auth');
//    }
//
//    /**
//     * Display a listing of the resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function index($user_id)
//    {
//        if($this->isAdminOrOwner($user_id)) {
//            $addresses = Address::where('user_id', $user_id)->get();
//            return response()->json($addresses);
//        }
//        else abort(403);
//    }
//
//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return \Illuminate\Http\Response
//     */
//    public function create(Request $request)
//    {
//        try {
//            $newAddress = Address::create($request->all());
//        }
//        catch (\Exception $e) {
//            return response()->json(['error' => 'Address already exists.'], 409);
//        }
//
//        return response()->json(['success' => 'Address created', 'address' => $newAddress], 200);
//    }
//
//    /**
//     * Store a newly created resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @return \Illuminate\Http\Response
//     */
//    public function store(Request $request)
//    {
//        //
//    }
//
//    /**
//     * Display the specified resource.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function show($user_id, $id)
//    {
//        if($this->isAdminOrOwner($user_id)) {
//            try {
//                $address = Address::where([['id', $id], ['user_id', $user_id]])->firstOrFail();
//                return response()->json($address);
//            }
//            catch (\Exception $e) {
//                return response()->json(['error' => 'Address not Found'], 404);
//            }
//        }
//        else abort(403);
//    }
//
//    /**
//     * Update the specified resource in storage.
//     *
//     * @param  \Illuminate\Http\Request  $request
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function update(Request $request, $user_id, $id)
//    {
//        if($this->isAdminOrOwner($user_id)) {
//            try {
//                $address = Address::findOrFail($id);
//            }
//            catch (\Exception $e) {
//                return response()->json(['error' => 'Address not Found'], 404);
//            }
//
//            $this->validate($request, [
//                'country_id' => 'required',
//                'street' => 'required',
//                'npa' => 'required',
//                'city' => 'required'
//            ]);
//
//            $input = $request->all();
//            $address->fill($input)->save();
//
//            return response()->json($address);
//        }
//        else abort(403);
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     *
//     * @param  int  $id
//     * @return \Illuminate\Http\Response
//     */
//    public function destroy($user_id, $id)
//    {
//        if($this->isAdminOrOwner($user_id)) {
//
//        }
//        else abort(403);
//    }
//}
