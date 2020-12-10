<?php

namespace App\Http\Library\Promo;

class Promo{

    private $WEB_URL_OFFICIAL = "https://shoesmart.co.id/";
    private $WEB_URL_API = "https://api.shoesmart.co.id/";

    public function getWebUrlOfficial(){
        return $this->WEB_URL_OFFICIAL;
    }

    public function getWebUrlApi(){
        return $this->WEB_URL_API;
    }
}
