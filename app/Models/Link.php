<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'user';
    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
