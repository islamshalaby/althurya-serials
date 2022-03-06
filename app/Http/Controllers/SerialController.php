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
        $this->middleware('auth:api' , ['except' => ['getValidProductSerials', 'deleteSerial', 'uploadSerial', 'getCountValidAllSerials', 'updateAmount', 'addlikeCardSerial', 'updateSerialsLikeCardProduct', 'updateSerialBought']]);
    }

    // get valid product serials
    public function getValidProductSerials(Request $request) {
        $data = Serial::where('product_id', $request->product_id)->where('sold', 0)->where('deleted', 0)->orderBy('like_product_id', 'asc')->get();

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
		ini_set('memory_limit', '-1');
        $file = Excel::import(new SerialImport, request()->excel_file);
		
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $file , 'ar');
        return response()->json($response , 200);
    }

    // get count vLid - all
    public function getCountValidAllSerials(Request $request) {
        $data['count_valid_serials'] = Serial::where('product_id', $request->product_id)->where('sold', 0)->where('deleted', 0)->count();
        $data['count_all_serials'] = Serial::where('product_id', $request->product_id)->where('deleted', 0)->count();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }

    // update amount
    public function updateAmount(Request $request) {
        $post = $request->all();
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

    // create serial bought by like card
    public function addlikeCardSerial(Request $request) {
        $post = $request->all();
        $productId = 0;
        if (isset($post['myproduct_id'])) {
            $productId = $post['myproduct_id'];
        }
        Serial::create(['like_product_id' => $post['product_id'],
        'serial' => $post['serial'],
         'valid_to' => $post['valid_to'],
         'serial_number' => $post['serial_number'],
         'product_id' => $productId
         ]);

         $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , 'ar');
         return response()->json($response , 200);
    }

    // update all serials added with like card by product
    public function updateSerialsLikeCardProduct(Request $request) {
        $serials = Serial::where('like_product_id', $request->like_product_id)->where('sold', 0)->where('deleted', 0)->get()
        ->map(function ($row) use ($request) {
            $row->product_id = $request->product_id;

            $row->save();
        });
        $data['count_valid'] = $serials->count();
        $data['count_all'] = Serial::where('like_product_id', $request->like_product_id)->count();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , 'ar');
        return response()->json($response , 200);
    }

    // update serial
    public function updateSerialBought(Request $request) {
        $serial = Serial::where('id', $request->serial_id)->first();
        $serial->update(['sold' => 1]);

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , 'ar');
        return response()->json($response , 200);
    }
}