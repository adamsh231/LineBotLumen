<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class ProductDetailImageColor extends ProductDetailImage{

    private $product_id;

    public function __construct($product_id)
    {
        $this->product_id = $product_id;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event, $index){
        $json = $this->templateProductDetail($index);

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Stock Detail',
                    'contents' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function templateProductDetail($index){
        $json = json_decode(file_get_contents(url('template/detail-image-color.json')), true);
        $api_product = $this->loadProduct($this->product_id);
        $variant_color = $api_product["variants"][$index];
        $product_name = $api_product["name"];
        $color_name = $api_product["variants"]["color"]["name"];

        $json["header"]["contents"][0]["text"] = $product_name;
        $json["body"]["contents"][0]["contents"][0]["url"] = $variant_color["image_urls"][0];
        $json["body"]["contents"][0]["contents"][1]["contents"][0]["url"] = $variant_color["image_urls"][1];
        $json["body"]["contents"][0]["contents"][1]["contents"][2]["url"] = $variant_color["image_urls"][2];

        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
