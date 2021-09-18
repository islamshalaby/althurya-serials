<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Address;
use App\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Favorite;
use App\Currency;
use App\ProductVip;

class SearchByNameController extends Controller
{
    
    public function Search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required'
        ]);

        if($validator->fails()){
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null, $request->lang);
            return response()->json($response , 406);
        }

        if (!$request->header('uniqueid') || empty($request->header('uniqueid'))) {
            $response = APIHelpers::createApiResponse(true , 406 , 'uniqueid required header' , 'unique_id required header' , null , $request->lang);
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

            $search = $request->search;

            $data = Product::where('products.deleted', 0)
            ->where('products.hidden', 0)
            ->Where(function($query) use ($search) {
                $query->Where('products.title_en', 'like', '%' . $search . '%')->orWhere('products.title_ar', 'like', '%' . $search . '%');
            })
            ->select('id', 'title_' . $request->lang . ' as title', 'offer', 'final_price', 'price_before_offer', 'offer_percentage')
            ->simplePaginate(12);
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


            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang) ;
            return response()->json($response , 200);
        }else {
            $response = APIHelpers::createApiResponse(true , 406 , 'Visitor is not exist or code country is empty' , 'Visitor is not exist or code country is empty' , null , $request->lang);
            return response()->json($response , 406);
        }
    }
}
