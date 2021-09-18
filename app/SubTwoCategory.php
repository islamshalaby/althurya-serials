<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubTwoCategory extends Model
{
    //
    protected $fillable = ['title_en', 'title_ar', 'image', 'deleted', 'sub_category_id'];


    public function category() {
        return $this->belongsTo('App\SubCategory', 'sub_category_id');
    }

    public function subCategories() {
        return $this->hasMany('App\SubThreeCategory', 'sub_category_id')->where('deleted', 0);
    }

    public function products() {
        return $this->hasMany('App\Product', 'sub_category_two_id')->where('deleted', 0);
    }
}
