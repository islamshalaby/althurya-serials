<?php

namespace App\Http\Controllers;

use App\Ad;
use Illuminate\Http\Request;
use App\Offer;
use App\Product;
use App\Category;
use App\OffersSection;
use App\ProductImage;
use App\Favorite;
use App\ControlOffer;
use App\Slider;
use App\Visitor;
use App\Address;
use App\DeliveryArea;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;


class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getoffers' , 'getoffersandroid', 'get_offers']]);
    }

    public function getoffers(Request $request){
        $offers_before = Offer::orderBy('sort' , 'ASC')->get();
        $offers = [];
        
        for($i = 0; $i < count($offers_before); $i++){
            if($offers_before[$i]['type'] == 1){
                $result = Product::find($offers_before[$i]['target_id']);
                if($result['deleted'] == 0 && $result['hidden'] == 0){
                    array_push($offers , $offers_before[$i]);
                }
            }else{
                $result = Category::find($offers_before[$i]['target_id']);
                if($result['deleted'] == 0 ){
                    array_push($offers , $offers_before[$i]);
                }
            }


        }

        $new_offers = [];
        for($i = 0; $i < count($offers); $i++){
            array_push($new_offers , $offers[$i]);
            if($offers[$i]->size == 3){
                if(count($offers) > 1 ){
                    if($offers[$i-1]->size != 3){
                        if(count($offers) > $i+1 ){
                            if($offers[$i+1]->size != 3){
                                $offer_element = new \stdClass();
                                $offer_element->id = 0;
                                $offer_element->image  = '';
                                $offer_element->size = 3;
                                $offer_element->type = 0;
                                $offer_element->target_id = 0;
                                $offer_element->sort = 0;
                                $offer_element->created_at = "";
                                $offer_element->updated_at = "";
                                array_push($new_offers , $offer_element);
                            }
                        }
                    }

                }
            }                        
        }
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $new_offers , $request->lang);
        return response()->json($response , 200);
    }

    public function getoffersandroid(Request $request){

        $offers_before = Offer::orderBy('sort' , 'ASC')->get();
        $offers = [];
        
        for($i = 0; $i < count($offers_before); $i++){
            if($offers_before[$i]['type'] == 1){
                $result = Product::find($offers_before[$i]['target_id']);
                if($result['deleted'] == 0 && $result['hidden'] == 0){
                    array_push($offers , $offers_before[$i]);
                }
            }else{
                $result = Category::find($offers_before[$i]['target_id']);
                if($result['deleted'] == 0){
                    array_push($offers , $offers_before[$i]);
                }
            }



        }

        $new_offers = [];
        for($i = 0; $i < count($offers); $i++){
            if($offers[$i]->size == 1 || $offers[$i]->size == 2 ){
                $count = count($new_offers);
                $new_offers[$count] = [];
                array_push($new_offers[$count] , $offers[$i]);
                $offer_element = new \stdClass();
                $offer_element->id = 0;
                $offer_element->image  = '';
                $offer_element->size = $offers[$i]->size;
                $offer_element->type = 0;
                $offer_element->target_id = 0;
                $offer_element->sort = 0;
                $offer_element->created_at = "";
                $offer_element->updated_at = "";
                array_push($new_offers[$count] , $offer_element);
            }

            if($offers[$i]->size == 3){

                if(count($offers) > 1 ){

                    $count_offers = count($new_offers);

                    $last_count = count($new_offers[$count_offers - 1]);
                    
                    if($last_count == 2){
                        $new_offers[$count_offers] = [];
                        array_push($new_offers[$count_offers] , $offers[$i]);
                        if(count($offers) > $i+1 ){
                             if($offers[$i+1]->size != 3){
                                $offer_element = new \stdClass();
                                $offer_element->id = 0;
                                $offer_element->image  = '';
                                $offer_element->size = 3;
                                $offer_element->type = 0;
                                $offer_element->target_id = 0;
                                $offer_element->sort = 0;
                                $offer_element->created_at = "";
                                $offer_element->updated_at = "";
                                array_push($new_offers[$count_offers] , $offer_element);
                            }
                        }else{
                            $offer_element = new \stdClass();
                            $offer_element->id = 0;
                            $offer_element->image  = '';
                            $offer_element->size = 3;
                            $offer_element->type = 0;
                            $offer_element->target_id = 0;
                            $offer_element->sort = 0;
                            $offer_element->created_at = "";
                            $offer_element->updated_at = "";
                            array_push($new_offers[$count_offers] , $offer_element);
                        }
                    }else{
                        array_push($new_offers[$count_offers - 1] , $offers[$i]);
                    }

                }else{
                    $count = count($new_offers);
                    $new_offers[$count] = [];
                    array_push($new_offers[$count] , $offers[$i]);
                    $offer_element = new \stdClass();
                    $offer_element->id = 0;
                    $offer_element->image  = '';
                    $offer_element->size = $offers[$i]->size;
                    $offer_element->type = 0;
                    $offer_element->target_id = 0;
                    $offer_element->sort = 0;
                    $offer_element->created_at = "";
                    $offer_element->updated_at = "";
                    array_push($new_offers[$count] , $offer_element);
                }
                
            }

        }

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $new_offers , $request->lang);
        return response()->json($response , 200);

    }

    public function get_offers(Request $request) {
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'unique_id Required Field' , 'unique_id Required Field' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();

        $address = Address::where('visitor_id', $visitor['id'])->first();
        $areaStores = DeliveryArea::where('area_id', $address['address_id'])->pluck('store_id')->toArray();

        $offers_sections = OffersSection::orderBy('sort', 'asc')->get();
        $offers = [];
        $data = [];
        for($i = 0; $i < count($offers_sections); $i++){
            $element = [];
            $element['icon'] = $offers_sections[$i]['icon'];
            $element['type'] = $offers_sections[$i]['type'];
            if($request->lang == 'en'){
                $element['title'] = $offers_sections[$i]['title_en'];
            }else{
                $element['title'] = $offers_sections[$i]['title_ar'];
            }
            $ids = ControlOffer::where('offers_section_id' , $offers_sections[$i]['id'])->pluck('offer_id');
            if($request->lang == 'en'){
                if ($offers_sections[$i]['type'] == 1) {
                    $element['ads'] = Ad::whereIn('id' , $ids)->get()->makeHidden(['store_id']);
                }else {
                    $element['ads'] = Product::select('id', 'title_en as title' , 'offer' , 'offer_percentage' , 'multi_options', 'final_price', 'price_before_offer', 'type')->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->whereIn('store_id', $areaStores)->whereIn('id' , $ids)->get()->makeHidden(['multiOptions']);
                }
                
            }else{
                if ($offers_sections[$i]['type'] == 1) {
                    $element['ads'] = Ad::whereIn('id' , $ids)->get()->makeHidden(['store_id']);
                }else {
                    $element['ads'] = Product::select('id', 'title_ar as title' , 'offer' , 'offer_percentage' , 'multi_options', 'final_price', 'price_before_offer', 'type')->where('deleted' , 0)->where('hidden' , 0)->where('remaining_quantity', '>', 0)->whereIn('store_id', $areaStores)->whereIn('id' , $ids)->get()->makeHidden(['multiOptions']);
                }
            }
            
            if ($offers_sections[$i]['type'] == 2) {
                for($j = 0; $j < count($element['ads']) ; $j++){
                    if(auth()->user()){
                        $user_id = auth()->user()->id;
                        
                        $prevfavorite = Favorite::where('product_id' , $element['ads'][$j]['id'])->where('user_id' , $user_id)->first();
                        if($prevfavorite){
                            $element['ads'][$j]['favorite'] = true;
                        }else{
                            $element['ads'][$j]['favorite'] = false;
                        }
    
                    }else{
                        $element['ads'][$j]['favorite'] = false;
                    }
                    $element['ads'][$j]['final_price'] = number_format((float)$element['ads'][$j]['final_price'], 3, '.', '');
                        $element['ads'][$j]['price_before_offer'] = number_format((float)$element['ads'][$j]['price_before_offer'], 3, '.', '');
                    $element['ads'][$j]['image'] = ProductImage::where('product_id' , $element['ads'][$j]['id'])->pluck('image')->first();
                }
            }
            

            array_push($offers , $element);
        }
        $data['offers'] = $offers;

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

}