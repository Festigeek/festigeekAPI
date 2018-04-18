<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('role:admin', ['except' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // TODO: prefer typeId instead of type_id, delete it with next front-end
        if($request->filled('type_id')) {
            $products = Product::where('product_type_id', $request->get('type_id'))->get();
        }
        else if($request->filled('typeId')) {
            $products = Product::where('product_type_id', $request->get('typeId'))->get();
        }
        // TODO: prefer typeName instead of type, delete it with next front-end
        else if ($request->filled('type')) {
            $type = $request->get('type');
            $products = Product::whereHas('product_type', function($query) use ($type) {
                $query->whereRaw('LOWER(name) LIKE ?' , $type);
            })->get();
        }
        else if ($request->filled('typeName')) {
            $type = $request->get('typeName');
            $products = Product::whereHas('product_type', function($query) use ($type) {
                $query->whereRaw('LOWER(name) LIKE ?' , $type);
            })->get();
        }
        else {
            $products = Product::all();
        }

        if($request->filled('eventId')) {
            $event_id = $request->get('eventId');
            $products = $products->filter(function($product) use ($event_id) {
                return $product->event_id == $event_id;
            });
        }

        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
