<?php

namespace App\Http\Controllers;

class Api extends Controller
{
    public function food(){
        return json_decode(file_get_contents(url('food.json')), true);
    }
}

