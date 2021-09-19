<?php
namespace App\Http\Controllers;

use App\Serial;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Excel;
use App\Imports\SerialImport;


class SerialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getValidProductSerials', 'deleteSerial', 'uploadSerial']]);
    }

    // get valid product serials
    public function getValidProductSerials(Request $request) {
        $data = Serial::where('product_id', $request->product_id)->where('sold', 0)->where('deleted', 0)->orderBy('id', 'desc')->get();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }

    // get all product serials
    public function getAllProductSerials(Request $request) {
        $data = Serial::where('product_id', $request->product_id)->orderBy('id', 'desc')->get();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }

    // delete serial
    public function deleteSerial(Request $request) {
        $data = Serial::where('id', $request->id)->first();
        if ($data) {
            $data->update(['deleted' => 1]);
            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
            return response()->json($response , 200);
        }else {
            $response = APIHelpers::createApiResponse(true , 406 , 'id not exist' , 'id not exist' , null , 'ar');
            return response()->json($response , 200);
        }
    }

    // upload serial
    public function uploadSerial(Request $request) {
        Excel::import(new SerialImport, request()->excel_file);

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , 'ar');
        return response()->json($response , 200);
    }
}