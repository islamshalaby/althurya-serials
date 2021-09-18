<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderSerial extends Model
{
    protected $fillable = ['order_id', 'serial_id'];

    public function serial() {
        return $this->belongsTo('App\Serial', 'serial_id');
    }
}