<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Product;
use App\Currency;
use Carbon\Carbon;
use App\ProductVip;
use App\Favorite;
use App\Visitor;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getdetails' , 'getproducts' , 'getbrandproducts', 'get_sub_category_products', 'getStoreProducts']]);
    }

    // product details
    public function getdetails(Request $request, $id){
        if (!$request->header('uniqueid') || empty($request->header('uniqueid'))) {
            $response = APIHelpers::createApiResponse(true , 406 , 'uniqueid required header' , 'uniqueid required header' , null , $request->lang);
            return response()->json($response , 406);
        }
        $visitor = Visitor::where('unique_id', $request->header('uniqueid'))->select('country_code')->first();
        if ($visitor && !empty($visitor->country_code)) {
            $currency = $visitor->country->currency_en;
            $toCurr = trim(strtolower($currency));
            if ($toCurr == "usd") {
                $currency = ["value" => 1];
            }else {
                $currency = Currency::where('from', "usd")->where('to', $toCurr)->first();
            }
            if (isset($currency['id'])) {
                if (!$currency->updated_at->isToday()) {
                    $result = APIHelpers::converCurruncy2("usd", $toCurr);
                    if(isset($result['value'])){
                        $currency->update(['value' => $result['value'], 'updated_at' => Carbon::now()]);
                        $currency = Currency::where('from', "usd")->where('to', $toCurr)->first();
                    }
                    
                }
                
            }else {
                
                if(!$currency){
                    $result = APIHelpers::converCurruncy2("usd", $toCurr);
                    $currency = Currency::create(['value' => $result['value'], "from" => "usd", "to" => $toCurr]);
                }
            }
            $data = Product::where('id', $id)->select('id', 'title_' . $request->lang . ' as title', 'offer', 'description_' . $request->lang . ' as description', 'final_price', 'price_before_offer', 'offer_percentage', 'category_id')->first()->makeHidden('category');
            
            if ($request->lang == 'en') {
                $data['category_name'] = $data->category->title_en;
            }else {
                $data['category_name'] = $data->category->title_ar;
            }
            $price = $data['final_price'] * $currency['value'];
            $priceBOffer = $data['price_before_offer'] * $currency['value'];
            if(auth()->user()){
                $user_id = auth()->user()->id;
                if (!empty(auth()->user()->vip_id)) {
                        
                    $productVip = ProductVip::where('vip_id', auth()->user()->vip_id)->where('product_id', $data['id'])->first();
                    if ($productVip) {
                        $priceOffer = $price * ($productVip->percentage / 100);
                        $price = $price - $priceOffer;
                        $priceBOfferOffer = $priceBOffer * ($productVip->percentage / 100);
                        $priceBOffer = $priceBOffer - $priceBOfferOffer;
                        $data['offer'] = 1;
                        $data['offer_percentage'] = $productVip->percentage;
                    }
    
                }
                $prevfavorite = Favorite::where('product_id' , $data['id'])->where('user_id' , $user_id)->first();
                if($prevfavorite){
                    $data['favorite'] = true;
                }else{
                    $data['favorite'] = false;
                }
    
            }else{
                $data['favorite'] = false;
            }
            $currencySympol = $visitor->country->currency_en;
            if ($request->lang == 'ar') {
                $currencySympol = $visitor->country->currency_ar;
            }
            $data['final_price'] = number_format((float)$price, 3, '.', '') . " " . $currencySympol;
            $data['price_before_offer'] = number_format((float)$priceBOffer, 3, '.', '') . " " . $currencySympol;
            for ($k = 0; $k < count($data->images); $k ++) {
                $data['images'][$k] = $data->images[$k]['image'];
            }
    
            $data['related_products'] = Product::where('deleted', 0)
                ->where('hidden', 0)
                ->where('reviewed', 1)
                ->where('remaining_quantity', '>', 0)
                ->where('id', '!=', $data['id'])
                ->where('category_id', $data['category_id'])
                ->select('id', 'title_' . $request->lang . ' as title', 'final_price', 'price_before_offer', 'offer_percentage')
                ->orderBy('id', 'desc')
                ->inRandomOrder()->limit(5)
                ->get()->makeHidden('mainImage');
    
            if (count($data['related_products']) > 0) {
                for ($r = 0; $r < count($data['related_products']); $r ++) {
                    $priceR = $data['related_products'][$r]['final_price'] * $currency['value'];
                    $priceBOfferR = $data['related_products'][$r]['price_before_offer'] * $currency['value'];
                    if(auth()->user()){
                        $user_id = auth()->user()->id;
                        if (!empty(auth()->user()->vip_id)) {
                        
                            $productVip = ProductVip::where('vip_id', auth()->user()->vip_id)->where('product_id', ['related_products'][$r]['id'])->first();
                            if ($productVip) {
                                $priceOffer = $price * ($productVip->percentage / 100);
                                $priceR = $price - $priceOffer;
                                $priceBOfferOffer = $priceBOffer * ($productVip->percentage / 100);
                                $priceBOfferR = $priceBOffer - $priceBOfferOffer;
                                $data['related_products'][$r]['offer'] = 1;
                                $data['related_products'][$r]['offer_percentage'] = $productVip->percentage;
                            }
        
                        }
                        $prevfavorite = Favorite::where('product_id' , $data['related_products'][$r]['id'])->where('user_id' , $user_id)->first();
                        if($prevfavorite){
                            $data['related_products'][$r]['favorite'] = true;
                        }else{
                            $data['related_products'][$r]['favorite'] = false;
                        }
                        $data['related_products'][$r]['final_price'] = number_format((float)$priceR, 3, '.', '')  . " " . $currencySympol;
                        $data['related_products'][$r]['price_before_offer'] = number_format((float)$priceBOfferR, 3, '.', '') . " " . $currencySympol;
                    }else{
                        $data['related_products'][$r]['favorite'] = false;
                    }
                    if ($data['related_products'][$r]->mainImage) {
                        $data['related_products'][$r]['image'] = $data['related_products'][$r]->mainImage['image'];
                    }else {
                        $data['related_products'][$r]['image'] = "";
                    }
                    $data['related_products'][$r]['final_price'] = number_format((float)$data['related_products'][$r]['final_price'], 3, '.', '') . " " . $currencySympol;
                    $data['related_products'][$r]['price_before_offer'] = number_format((float)$data['related_products'][$r]['price_before_offer'], 3, '.', '') . " " . $currencySympol;
                    
                }
            }
            
    
            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
            return response()->json($response , 200);
        }else {
            $response = APIHelpers::createApiResponse(true , 406 , 'Visitor is not exist or code country is empty' , 'Visitor is not exist or code country is empty' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    // get products
    public function getproducts(Request $request){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        if (!$request->header('uniqueid') || empty($request->header('uniqueid'))) {
            $response = APIHelpers::createApiResponse(true , 406 , 'uniqueid required header' , 'uniqueid required header' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id', $request->header('uniqueid'))->select('country_code')->first();
        if ($visitor && !empty($visitor->country_code)) {
            $currency = $visitor->country->currency_en;
            $toCurr = trim(strtolower($currency));
            if ($toCurr == "usd") {
                $currency = ["value" => 1];
            }else {
                $currency = Currency::where('from', "usd")->where('to', $toCurr)->first();
            }
            if (isset($currency['id'])) {
                if (!$currency->updated_at->isToday()) {
                    $result = APIHelpers::converCurruncy2("usd", $toCurr);
                    if(isset($result['value'])){
                        $currency->update(['value' => $result['value'], 'updated_at' => Carbon::now()]);
                        $currency = Currency::where('from', "usd")->where('to', $toCurr)->first();
                    }
                    
                }
                
            }else {
                $result = APIHelpers::converCurruncy2("usd", $toCurr);
                // dd($result);
                if(isset($result['value']) && !$currency){
                    $result = APIHelpers::converCurruncy2("usd", $toCurr);
                    $currency = Currency::create(['value' => $result['value'], "from" => "usd", "to" => $toCurr]);
                }
            }
    
            $data = Product::where('deleted', 0)->where('hidden', 0)->where('category_id', $request->category_id);
            
            if (isset($request->sub_category_id) && $request->sub_category_id != 0) {
                $data = $data->where('sub_category_id', $request->sub_category_id);
            }
    
            if (isset($request->sub_category_two_id) && $request->sub_category_two_id != 0) {
                $data = $data->where('sub_category_two_id', $request->sub_category_two_id);
            }
    
            if (isset($request->sub_category_three_id) && $request->sub_category_three_id != 0) {
                $data = $data->where('sub_category_three_id', $request->sub_category_three_id);
            }
    
            if (isset($request->sub_category_four_id) && $request->sub_category_four_id != 0) {
                $data = $data->where('sub_category_four_id', $request->sub_category_four_id);
            }
    
            if (isset($request->sub_category_five_id) && $request->sub_category_five_id != 0) {
                $data = $data->where('sub_category_five_id', $request->sub_category_five_id);
            }
    
            $data = $data->select('id', 'title_' . $request->lang . ' as title', 'offer', 'final_price', 'price_before_offer', 'offer_percentage')->orderBy('id','desc')->simplePaginate(12);
            $data->makeHidden('images');
    
            if (count($data) > 0) {
                for ($i = 0; $i < count($data); $i ++) {
                    if ($data[$i]->main_image) {
                        $data[$i]->main_image = $data[$i]->main_image->image;
                    }else {
                        if (count($data[$i]->images) > 0) {
                            $data[$i]->main_image = $data[$i]->images[0]->image;
                        }
                    }
                    $price = $data[$i]['final_price'] * $currency['value'];
                    $priceBOffer = $data[$i]['price_before_offer'] * $currency['value'];
    
                    $user = auth()->user();
                    if($user){
                        if (!empty(auth()->user()->vip_id)) {
                            $productVip = ProductVip::where('vip_id', auth()->user()->vip_id)->where('product_id', $data[$i]['id'])->first();
                            if ($productVip) {
                                $priceOffer = $price * ($productVip->percentage / 100);
                                $price = $price - $priceOffer;
                                $priceBOfferOffer = $priceBOffer * ($productVip->percentage / 100);
                                $priceBOffer = $priceBOffer - $priceBOfferOffer;
                                $data[$i]['offer'] = 1;
                                $data[$i]['offer_percentage'] = $productVip->percentage;
                            }
                        }
                        $favorite = Favorite::where('user_id' , $user->id)->where('product_id' , $data[$i]['id'])->first();
                        if($favorite){
                            $data[$i]['favorite'] = true;
                        }else{
                            $data[$i]['favorite'] = false;
                        }
                    }else{
                        $data[$i]['favorite'] = false;
                    }
                    $currencySympol = $visitor->country->currency_en;
                    if ($request->lang == 'ar') {
                        $currencySympol = $visitor->country->currency_ar;
                    }
                    $data[$i]['final_price'] = number_format((float)$price, 3, '.', '') . " " . $currencySympol;
                    $data[$i]['price_before_offer'] = number_format((float)$priceBOffer, 3, '.', '') . " " . $currencySympol;
                }
            }
    
            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
            return response()->json($response , 200);
        }else {
            $response = APIHelpers::createApiResponse(true , 406 , 'Visitor is not exist or code country is empty' , 'Visitor is not exist or code country is empty' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    

}