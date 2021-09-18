<?php

namespace App\Http\Controllers;
use App\Helpers\APIHelpers;

use App\Serial;
use Illuminate\Http\Request;

class SerialController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getValidProductSerials']]);
    }

    // get product serials
    public function getValidProductSerials(Request $request) {
        
        $data = Serial::where('product_id', $request->product_id)->where('sold', 0)->where('deleted', 0)->orderBy('id', 'desc')->get();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }
}