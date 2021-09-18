<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
// use Illuminate\Support\Facades\Auth;
use App\Category;
use App\Serial;
use App\SubCategory;
use App\SubFourCategory;
use App\SubTwoCategory;
use App\SubThreeCategory;
use App\SubFiveCategory;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // get permissions of current admin
        date_default_timezone_set('Asia/Kuwait');
        // delete expired serials
        Serial::where('deleted', 0)->where('sold', 0)->get()
        ->map(function ($row) {
            $validTo = Carbon::parse($row->valid_to);
            if ($validTo->isPast()) {
                $row->deleted = 1;
                $row->save();
                $row->product->update(['remaining_quantity' => $row->product->remaining_quantity - 1]);
            }
        });
        
        // categories
        $cats = Category::where('deleted', 0)->has('products', '>', 0)->select('id', 'title_en', 'title_ar', 'image')->get()->makeHidden('subCategories')
        ->map(function ($cat) {
            $cat->next_level = false;
            
            if ($cat->subCategories && count($cat->subCategories) > 0) {
                $hasProducts = false;
                for ($i = 0; $i < count($cat->subCategories); $i++) {
                    if (count($cat->subCategories[$i]->products) > 0) {
                        $hasProducts = true;
                    }
                }

                if ($hasProducts) {
                    $cat->next_level = true;

                    $cat->sub_categories = SubCategory::where('deleted' , 0)->where('category_id' , $cat->id)->has('products', '>', 0)->select('id' , 'image' , 'title_ar','title_en', 'category_id')->get()
                    ->makeHidden('subCategories')
                    ->map(function($sCat) {
                        $sCat->next_level = false;
                        
                        if (count($sCat->subCategories) > 0) {
                            $hasProducts = false;
                            for ($i = 0; $i < count($sCat->subCategories); $i ++) {
                                if (count($sCat->subCategories[$i]->products) > 0) {
                                    $hasProducts = true;
                                }
                            }
            
                            if ($hasProducts) {
                                $sCat->next_level = true;

                                $sCat->sub_categories = SubTwoCategory::where('deleted' , 0)->where('sub_category_id' , $sCat->id)->has('products', '>', 0)->select('id' , 'image' , 'title_en' , 'title_ar', 'sub_category_id')->get()
                                ->makeHidden(['subCategories', 'category'])
                                ->map(function($sCat2){
                                    $sCat2->next_level = false;
                                    
                                    if (count($sCat2->subCategories) > 0) {
                                        $hasProducts = false;
                                        for ($i = 0; $i < count($sCat2->subCategories); $i ++) {
                                            if (count($sCat2->subCategories[$i]->products) > 0) {
                                                $hasProducts = true;
                                            }
                                        }
                        
                                        if ($hasProducts) {
                                            $sCat2->next_level = true;
                                            $sCat2->sub_categories = SubThreeCategory::where('deleted' , 0)->where('sub_category_id' , $sCat2->id)->has('products', '>', 0)->select('id' , 'image' , 'title_en' ,'title_ar', 'sub_category_id')->get()
                                            ->makeHidden(['subCategories', 'category'])
                                            ->map(function($sCat3) {
                                                $sCat3->next_level = false;
                                                
                                                if (count($sCat3->subCategories) > 0) {
                                                    $hasProducts = false;
                                                    for ($i = 0; $i < count($sCat3->subCategories); $i ++) {
                                                        if (count($sCat3->subCategories[$i]->products) > 0) {
                                                            $hasProducts = true;
                                                        }
                                                    }
                                    
                                                    if ($hasProducts) {
                                                        $sCat3->next_level = true;
                                                        $sCat3->sub_categories = SubFourCategory::where('deleted' , 0)->where('sub_category_id' , $sCat3->id)->has('products', '>', 0)->select('id' , 'image' , 'title_en', 'title_ar', 'sub_category_id')->get()
                                                        ->makeHidden(['subCategories', 'category'])
                                                        ->map(function($sCat4) {
                                                            $sCat4->next_level = false;
                                                            
                                                            if (count($sCat4->subCategories) > 0) {
                                                                $hasProducts = false;
                                                                for ($i = 0; $i < count($sCat4->subCategories); $i ++) {
                                                                    if (count($sCat4->subCategories[$i]->products) > 0) {
                                                                        $hasProducts = true;
                                                                    }
                                                                }
                                                
                                                                if ($hasProducts) {
                                                                    $sCat4->next_level = true;
                                                                    $sCat4->sub_categories = SubFiveCategory::where('deleted' , 0)->where('sub_category_id' , $sCat4->id)->has('products', '>', 0)->select('id' , 'image' , 'title_en', 'title_ar', 'sub_category_id')->get()
                                                                    ->makeHidden(['subCategories', 'category']);
                                                                }
                                                                
                                                            }
                                                
                                                            return $sCat4;
                                                        });
                                                    }
                                                    
                                                }
                                    
                                                return $sCat3;
                                            });
                                        }
                                        
                                    }
                        
                                    return $sCat2;
                                });
                        
                            }
                            
                        }
            
                        return $sCat;
                    });
                }

            }

            return $cat;
        });
        config(['cats' => $cats]);
    }
}
