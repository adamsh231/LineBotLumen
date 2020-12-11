<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class Promo
{
    private $product;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->product = new Product;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event)
    {
        $json = $this->templatePromoList();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'List Promo',
                    'contents' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//
    private function loadPromo()
    {
        //!! Warning !! flex message carousel max? !!//
        $api_promo = $this->product->getWebUrlApi() . "promonew/query?_order=id&_sort=desc&_is_paginate=0&_paginate=15&page=&is_active=1&is_displayed=1";
        $api_promo = $this->httpClient->get($api_promo);
        $api_promo = json_decode($api_promo->getRawBody(), true);
        return $api_promo;
    }

    private function templatePromoList()
    {
        $json = json_decode(file_get_contents(url('template/promo-list.json')), true);
        $api_promo = $this->loadPromo();
        foreach ($api_promo as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["hero"]["url"] = $value["image_square"];
            $json["contents"][$key]["body"]["contents"][0]["text"] = $value["name"];
            $json["contents"][$key]["body"]["contents"][1]["text"] = $value["description_short"];

            $tanggal = $this->viewPeriodePromo($value["start"], $value["stop"]);
            $json["contents"][$key]["footer"]["contents"][1]["contents"][0]["contents"][1]["text"] = $tanggal;

            $json["contents"][$key]["footer"]["contents"][1]["contents"][1]["contents"][0]["action"]["uri"] = $this->product->getWebUrlOfficial() . "Promo/detail/" . $value["id"];
        }
        return $json;
    }

    private function viewPeriodePromo($start, $stop)
    {
        $start = strtotime(explode(' ', $start)[0]);
        $stop = strtotime(explode(' ', $stop)[0]);
        $format1 = date('d M', $start) . " - " . date('d M Y', $stop);
        $format2 = date('d', $start) . " - " . date('d F Y', $stop);
        $month_start = explode(' ', $format1)[1];
        $month_stop = explode(' ', $format1)[4];
        $tanggal = ($month_start == $month_stop) ? $format2 : $format1;
        return $tanggal;
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

}
