<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class ProductDetailImageColor extends ProductDetailImage{

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event, $id){
        $json = $this->templateProductDetail($id);

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

    private function templateProductDetail($id){
        $json = json_decode(file_get_contents(url('template/detail-image-color.json')), true);
        // $api_product = $this->loadProduct($id);
        // $variants = $api_product["variants"];
        // foreach ($variants as $key => $value) {
        //     $json["columns"][$key] = $json["columns"][0];
        //     $json["columns"][$key]["imageUrl"] = $value["image_urls"][0];
        //     $json["columns"][$key]["action"]["label"] = $value["color"]["name"];
        // }
        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
