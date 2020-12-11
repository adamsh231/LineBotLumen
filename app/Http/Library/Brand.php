<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class Brand
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
        $json = $this->templateBrand();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'List Brand',
                    'contents' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//
    private function loadBrand()
    {
        $api_brand = $this->product->getWebUrlApi() . "brands?_sort=id&_order=asc&_start=0&_end=4"; //!! ASC -> Limit 4
        $api_brand = $this->httpClient->get($api_brand);
        $api_brand = json_decode($api_brand->getRawBody(), true);
        return $api_brand;
    }

    private function templateBrand()
    {
        $json = json_decode(file_get_contents(url('template/brand.json')), true);
        $api_brand = $this->loadBrand();

        foreach($api_brand as $key => $value){
            $json["body"]["contents"][$key] = $json["body"]["contents"][0];
            $json["body"]["contents"][$key]["url"] = $value["image"];
        }

        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
