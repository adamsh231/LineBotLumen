<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class ProductDetailImage{

    private $product;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->product = new Product;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event){
        $json = $this->templateProductDetail();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'template',
                    'altText'  => 'Product Detail Image Carousel',
                    'template' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function loadProduct($id = 2276)
    {
        $api_product = $this->product->getWebUrlApi() . "products/" . $id;
        $api_product = $this->httpClient->get($api_product);
        $api_product = json_decode($api_product->getRawBody(), true);
        return $api_product;
    }

    private function templateProductDetail(){
        $json = json_decode(file_get_contents(url('template/detail-image.json')), true);
        $api_product = $this->loadProduct();
        $variants = $api_product["variants"];
        foreach ($variants as $key => $value) {
            $json["columns"][$key] = $json["columns"][0];
            $json["columns"][$key]["imageUrl"] = $value["image_urls"][0];
            $json["columns"][$key]["action"]["text"] = $value["color"]["name"];
        }
        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
