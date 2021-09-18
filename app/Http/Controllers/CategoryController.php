<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIHelpers;
use App\Category;
use App\SubCategory;
use App\SubFiveCategory;
use App\SubFourCategory;
use App\SubThreeCategory;
use App\SubTwoCategory;
use App\SliderAd;
use App\Ad;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api' , ['except' => ['getcategories' , 'get_sub_categories', 'getMerchantCategories', 'getCategoriesSlider', 'getSubCategories', 'getSubTwoCategories', 'getSubThreeCategories', 'getSubFourCategories', 'getSubFiveCategories']]);
    }
	
	public function getCategories(Request $request) {
        $data = $this->getCats($request, 'api');
        

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    /**
     * Merchant code
     */
    
    
	// get merchant categories
    public function getMerchantCategories(Request $request) {
        $lang = 1;
        // if (App::isLocale('ar')) {
        //     $lang = 2;
        // }
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://taxes.like4app.com/online/categories",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array(
            'deviceId' => env("LIKECARD_DEVICE_ID"),
            'email' => env("LIKECARD_EMAIL"),
            'password' => env("LIKECARD_PASSWORD"),
            'securityCode' => env("LIKECARD_SECURITY_CODE"),
            'langId' => $lang
        )
        ));

        $response = curl_exec($curl);

        $response = json_decode($response);

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $response , $request->lang);
            return response()->json($response , 200);
    }

    // get subcategories
    public function getSubCategories(Request $request) {
        $data = SubCategory::where('deleted' , 0)->where('category_id' , $request->category_id)->has('products', '>', 0)->select('id' , 'image' , 'title_en as title', 'category_id')->get()
        ->makeHidden('subCategories')
        ->map(function($sCat) use ($request){
            $sCat->next_level = false;
            $root_url = $request->root();
            $sCat->url = $root_url . '/api/products/show/en/v1?category_id=' . $sCat->category_id . '&sub_category_id=' . $sCat->id;
            if (count($sCat->subCategories) > 0) {
                $hasProducts = false;
                for ($i = 0; $i < count($sCat->subCategories); $i ++) {
                    if (count($sCat->subCategories[$i]->products) > 0) {
                        $hasProducts = true;
                    }
                }

                if ($hasProducts) {
                    $sCat->next_level = true;
                    $sCat->url = $root_url . '/api/categories/' . $sCat->id . '/sub-categories-two/en/v1';
                }
                
            }

            return $sCat;
        });
        
            
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    // get subcategories two
    public function getSubTwoCategories(Request $request) {
        $data = SubTwoCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->has('products', '>', 0)->select('id' , 'image' , 'title_en as title', 'sub_category_id')->get()
        ->makeHidden(['subCategories', 'category'])
        ->map(function($sCat) use ($request){
            $sCat->next_level = false;
            $root_url = $request->root();
            $sCat->url = $root_url . '/api/products/show/en/v1?category_id=' . $sCat->category->category_id . '&sub_category_id=' . $sCat->sub_category_id . '&sub_category_two_id=' . $sCat->id;
            if (count($sCat->subCategories) > 0) {
                $hasProducts = false;
                for ($i = 0; $i < count($sCat->subCategories); $i ++) {
                    if (count($sCat->subCategories[$i]->products) > 0) {
                        $hasProducts = true;
                    }
                }
                if ($hasProducts) {
                    $sCat->next_level = true;
                    $sCat->url = $root_url . '/api/categories/' . $sCat->id . '/sub-categories-three/en/v1';
                }
                
            }

            return $sCat;
        });
        
            
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    // get subcategories three
    public function getSubThreeCategories(Request $request) {
        $data = SubThreeCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->has('products', '>', 0)->select('id' , 'image' , 'title_en as title', 'sub_category_id')->get()
        ->makeHidden(['subCategories', 'category'])
        ->map(function($sCat) use ($request){
            $sCat->next_level = false;
            $root_url = $request->root();
            $sCat->url = $root_url . '/api/products/show/en/v1?category_id=' . $sCat->category->category->category_id . '&sub_category_id=' . $sCat->category->category->id . '&sub_category_two_id=' . $sCat->sub_category_id . '&sub_category_three_id=' . $sCat->id;
            if (count($sCat->subCategories) > 0) {
                $hasProducts = false;
                for ($i = 0; $i < count($sCat->subCategories); $i ++) {
                    if (count($sCat->subCategories[$i]->products) > 0) {
                        $hasProducts = true;
                    }
                }

                if ($hasProducts) {
                    $sCat->next_level = true;
                    $sCat->url = $root_url . '/api/categories/' . $sCat->id . '/sub-categories-four/en/v1';
                }

            }

            return $sCat;
        });
        
            
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    // get subcategories four
    public function getSubFourCategories(Request $request) {
        $data = SubFourCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->has('products', '>', 0)->select('id' , 'image' , 'title_en as title', 'sub_category_id')->get()
        ->makeHidden(['subCategories', 'category'])
        ->map(function($sCat) use ($request){
            $sCat->next_level = false;
            $root_url = $request->root();
            $sCat->url = $root_url . '/api/products/show/en/v1?category_id=' . $sCat->category->category->category->category_id 
            . '&sub_category_id=' . $sCat->category->category->category->id 
            . '&sub_category_two_id=' . $sCat->category->sub_category_id 
            . '&sub_category_three_id=' . $sCat->sub_category_id 
            . '&sub_category_four_id=' . $sCat->id;
            if (count($sCat->subCategories) > 0) {
                $hasProducts = false;
                for ($i = 0; $i < count($sCat->subCategories); $i ++) {
                    if (count($sCat->subCategories[$i]->products) > 0) {
                        $hasProducts = true;
                    }
                }

                if ($hasProducts) {
                    $sCat->next_level = true;
                    $sCat->url = $root_url . '/api/categories/' . $sCat->id . '/sub-categories-five/en/v1';
                }
                
            }

            return $sCat;
        });
        
            
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    // get subcategories five
    public function getSubFiveCategories(Request $request) {
        $data = SubFiveCategory::where('deleted' , 0)->where('sub_category_id' , $request->sub_category_id)->has('products', '>', 0)->select('id' , 'image' , 'title_en as title', 'sub_category_id')->get()
        ->makeHidden(['subCategories', 'category'])
        ->map(function($sCat) use ($request){
            $sCat->next_level = false;
            $root_url = $request->root();
            $sCat->url = $root_url . '/api/products/show/en/v1?category_id=' . $sCat->category->category->category->category->category_id 
            . '&sub_category_id=' . $sCat->category->category->category->category->id 
            . '&sub_category_two_id=' . $sCat->category->category->sub_category_id 
            . '&sub_category_three_id=' . $sCat->category->sub_category_id 
            . '&sub_category_four_id=' . $sCat->sub_category_id
            . '&sub_category_five_id=' . $sCat->id;

            return $sCat;
        });
        
            
        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $data , $request->lang);
        return response()->json($response , 200);
    }

    // get categories slider
    public function getCategoriesSlider(Request $request) {
        $ads = $this->getCategorySlider();

        $response = APIHelpers::createApiResponse(false , 200 , '' , '' , $ads , $request->lang);
        return response()->json($response , 200);
    }

}    