<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\APIHelpers;
use App\Visitor;
use App\Cart;
use App\Favorite;
use App\Product;
use App\Currency;
use App\ProductVip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['create' , 'add' , 'delete' , 'get' , 'changecount' , 'getcartcount']]);
    }

    // create visitor 
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'fcm_token' => "required",
            'type' => 'required', // 1 -> iphone ---- 2 -> android
            'country_code' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $last_visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($last_visitor){
            $last_visitor->fcm_token = $request->fcm_token;
            $last_visitor->country_code = strtoupper($request->country_code);
            $last_visitor->save();
            $visitor = $last_visitor;
        }else{
            $visitor = new Visitor();
            $visitor->unique_id = $request->unique_id;
            $visitor->fcm_token = $request->fcm_token;
            $visitor->type = $request->type;
            $visitor->country_code = strtoupper($request->country_code);
            $visitor->save();
        }


        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $visitor , $request->lang);
        return response()->json($response , 200);
    }

    // add to cart
    public function add(Request $request){
        $validator = Validator::make($request->all(), [
            // 'unique_id' => 'required',
            'product_id' => 'required|exists:products,id'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields or product does not exist' , 'بعض الحقول مفقودة او المنتج غير موجود' , null , $request->lang);
            return response()->json($response , 406);
        }

        $product = Product::find($request->product_id);
        

        $visitor = Visitor::where('unique_id' , $request->header('uniqueid'))->first();
        if($visitor){
            
            $cart = Cart::where('visitor_id' , $visitor->id)->where('product_id' , $request->product_id)->first();
           
            
            if($cart){
                if($product->remaining_quantity < $cart->count + 1){
                    $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                    return response()->json($response , 406);
                }
                $count = $cart->count;
                $cart->count = $count + 1;
                $cart->save();
            }else{
                if($product->remaining_quantity < 1){
                    $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
                    return response()->json($response , 406);
                }
                $cart = new Cart();
                $cart->count = 1;
                $cart->product_id = $request->product_id;
                $cart->visitor_id = $visitor->id;
                $cart->save();
            }
            

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $cart , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }

    }

    // remove from cart
    public function delete(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'product_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){
            
            $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->first();
            
            $cart->delete();

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , null , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    // get cart
    public function get(Request $request){
        
        if (!$request->header('uniqueid')) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }
        
        $visitor = Visitor::where('unique_id' , $request->header('uniqueid'))->first();
        
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
                if(isset($result['value']) && !$currency){
                    $result = APIHelpers::converCurruncy2("usd", $toCurr);
                    $currency = Currency::create(['value' => $result['value'], "from" => "usd", "to" => $toCurr]);
                }
            }
            $currencySympol = $visitor->country->currency_en;
            if ($request->lang == 'ar') {
                $currencySympol = $visitor->country->currency_ar;
            }
            $visitor_id =  $visitor['id'];
            $data['cart'] = [];
            $data['total'] = '0.000';
            if ($request->product_id && $request->product_id != 0) {
                $product = Product::where('id', $request->product_id)
                ->select('id', 'title_' . $request->lang . ' as title', 'offer', 'final_price', 'price_before_offer', 'offer_percentage')
                ->first()
                ->makeHidden('images');
                $product['count'] = 1;
                $price = $product['final_price'];
                $priceBOffer = $product['price_before_offer'] * $currency['value'];
                
                if ($product->main_image) {
                    $product->main_image = $product->main_image->image;
                }else {
                    if (count($product->images) > 0) {
                        $product->main_image = $product->images[0]->image;
                    }
                }
                $user = auth()->user();
                if($user){
                    if (!empty(auth()->user()->vip_id)) {
                        $productVip = ProductVip::where('vip_id', auth()->user()->vip_id)->where('product_id', $product['id'])->first();
                        if ($productVip) {
                            $priceOffer = $price * ($productVip->percentage / 100);
                            $price = $price - $priceOffer;
                            $priceBOffer = $product['final_price'] * $currency['value'];
                            $product['offer'] = 1;
                            $product['offer_percentage'] = $productVip->percentage;
                        }
                    }
                    $favorite = Favorite::where('user_id' , $user->id)->where('product_id' , $product->id)->first();
                    if($favorite){
                        $product->favorite = true;
                    }else{
                        $product->favorite = false;
                    }
                }else{
                    $product->favorite = false;
                }
                
                $data['total'] = $data['total'] + number_format((float)$price, 3, '.', '');
                // dd($data['total']);
                $data['total'] = $data['total'] * $currency['value'];
                $product['final_price'] = number_format((float)$price, 3, '.', '') * $currency['value'];
                $product['final_price'] = number_format((float)$product['final_price'], 3, '.', '') . " " . $currencySympol;
                $product['price_before_offer'] = number_format((float)$priceBOffer, 3, '.', '') . " " . $currencySympol;
                array_push($data['cart'], $product);
            }else {
                $cart = Cart::where('visitor_id' , $visitor_id)->select('product_id as id' , 'count')->get();
                if (count($cart) > 0) {
                    for ($i = 0; $i < count($cart); $i ++) {
                        $product = Product::where('id', $cart[$i]['id'])
                        ->select('id', 'title_' . $request->lang . ' as title', 'offer', 'final_price', 'price_before_offer', 'offer_percentage')
                        ->first()
                        ->makeHidden('images');
                        $product['count'] = $cart[$i]['count'];
                        $price = $product['final_price'];
                        $priceBOffer = $product['price_before_offer'] * $currency['value'];
                        
                        if ($product->main_image) {
                            $product->main_image = $product->main_image->image;
                        }else {
                            if (count($product->images) > 0) {
                                $product->main_image = $product->images[0]->image;
                            }
                        }
                        $user = auth()->user();
                        if($user){
                            if (!empty(auth()->user()->vip_id)) {
                                $productVip = ProductVip::where('vip_id', auth()->user()->vip_id)->where('product_id', $product['id'])->first();
                                if ($productVip) {
                                    $priceOffer = $price * ($productVip->percentage / 100);
                                    $price = $price - $priceOffer;
                                    $priceBOffer = $product['final_price'] * $currency['value'];
                                    $product['offer'] = 1;
                                    $product['offer_percentage'] = $productVip->percentage;
                                }
                            }
                            $favorite = Favorite::where('user_id' , $user->id)->where('product_id' , $product->id)->first();
                            if($favorite){
                                $product->favorite = true;
                            }else{
                                $product->favorite = false;
                            }
                        }else{
                            $product->favorite = false;
                        }
                        $data['total'] = $data['total'] + ($price * $cart[$i]['count']);
                        $data['total'] = number_format((float)$data['total'], 3, '.', '') * $currency['value'];
                        $product['final_price'] = number_format((float)$price, 3, '.', '') * $currency['value'];
                        $product['final_price'] = number_format((float)$product['final_price'], 3, '.', '') . " " . $currencySympol;
                        $product['price_before_offer'] = number_format((float)$priceBOffer, 3, '.', '') . " " . $currencySympol;
    
                        array_push($data['cart'], $product);
                    }
                }
            }
            
            $data['email'] = "";
            if (auth()->user()) {
                $data['email'] = auth()->user()->email;
            }

            $data['total'] = number_format((float)$data['total'], 3, '.', '') . " " . $currencySympol;
            
            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
            return response()->json($response , 200);
        }else {
            $response = APIHelpers::createApiResponse(true , 406 , 'Visitor is not exist or code country is empty' , 'Visitor is not exist or code country is empty' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    // get cart count 
    public function getcartcount(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields' , 'بعض الحقول مفقودة' , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        if($visitor){
            $visitor_id =  $visitor['id'];
            $cart = Cart::where('visitor_id' , $visitor_id)->select('product_id as id' , 'count')->get();
            $count['count'] = count($cart);

            $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $count , $request->lang);
            return response()->json($response , 200);

        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
    }

    // change count
    public function changecount(Request $request){
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required',
            'product_id' => 'required|exists:products,id',
            'new_count' => 'required'
        ]);

        if ($validator->fails()) {
            $response = APIHelpers::createApiResponse(true , 406 , 'Missing Required Fields or product does not exist' , 'بعض الحقول مفقودة او المنتج غير موجود'  , null , $request->lang);
            return response()->json($response , 406);
        }

        $visitor = Visitor::where('unique_id' , $request->unique_id)->first();
        
        $product = Product::find($request->product_id);
        if($product->remaining_quantity < $request->new_count){
            $response = APIHelpers::createApiResponse(true , 406 , 'The remaining amount of the product is not enough' , 'الكميه المتبقيه من المنتج غير كافيه'  , null , $request->lang);
            return response()->json($response , 406);
        }
        
        

        if($visitor){
            
            $cart = Cart::where('product_id' , $request->product_id)->where('visitor_id' , $visitor->id)->first()->makeHidden('option_id');
            
            
            if (isset($cart->count)) {
                $cart->count = $request->new_count;
                $cart->save();
                $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $cart , $request->lang);
                return response()->json($response , 200);
            }else {
                $response = APIHelpers::createApiResponse(true , 406 , 'This product is not exist in cart' , 'هذا المنتج غير موجود بالعربة' , null , $request->lang);
                return response()->json($response , 406);
            }
        }else{
            $response = APIHelpers::createApiResponse(true , 406 , 'This Unique Id Not Registered' , 'This Unique Id Not Registered' , null , $request->lang);
            return response()->json($response , 406);
        }
        
    }
}