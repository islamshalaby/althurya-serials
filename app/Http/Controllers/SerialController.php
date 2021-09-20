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
        $this->middleware('auth:api' , ['except' => ['getValidProductSerials', 'deleteSerial', 'uploadSerial', 'getCountValidAllSerials', 'updateAmount']]);
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

    // get count vLid - all
    public function getCountValidAllSerials(Request $request) {
        $data['count_valid_serials'] = Serial::where('product_id', $request->product_id)->where('sold', 0)->where('deleted', 0)->count();
        $data['count_all_serials'] = Serial::where('product_id', $request->product_id)->count();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }

    // update amount
    public function updateAmount(Request $request) {
        $post = $request->all();
        $request->validate([
            'total_quatity' => 'required',
            'serials.*' => ['required', 'unique:serials,serial', 'distinct'], // distinct - to not repeat value
            'valid_to' => 'required'
        ]);
        
        if ($post['serials'] && count($post['serials']) > 0) {
            
            for ($i = 0; $i < count($post['serials']); $i ++) {
                
                Serial::create(['product_id' => $post['product_id'],
                 'serial' => $post['serials'][$i],
                  'valid_to' => $post['valid_to'][$i]
                  ]);
            }
        }

        
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , 'ar');
        return response()->json($response , 200);
    }
}