<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = ['user_id', 'product_id'];

    public function product() {
        return $this->belongsTo('App\Product', 'product_id')->where('deleted', 0)->where('hidden', 0);
    }
}
