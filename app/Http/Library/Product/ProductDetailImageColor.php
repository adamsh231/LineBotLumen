<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;
use App\Http\Library\Command;

class ProductDetailImageColor{

    private $product;
    private $command;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->product = new Product;
        $this->command = new Command;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event, $index, $id){
        $json = $this->templateProductDetail($id, $index);

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

    protected function loadProduct($id)
    {
        $api_product = $this->product->getWebUrlApi() . "products/" . $id;
        $api_product = $this->httpClient->get($api_product);
        $api_product = json_decode($api_product->getRawBody(), true);
        return $api_product;
    }

    private function templateProductDetail($id, $index){
        $json = json_decode(file_get_contents(url('template/detail-image-color.json')), true);
        $api_product = $this->loadProduct($id);
        $variant_color = $api_product["variants"][$index];
        $color_name = $variant_color["color"]["name"];
        $product_name = $api_product["name"];

        $json["header"]["contents"][0]["text"] = $product_name;
        $json["body"]["contents"][0]["contents"][0]["url"] = $variant_color["image_urls"][0];
        $json["body"]["contents"][0]["contents"][1]["contents"][0]["url"] = $variant_color["image_urls"][1];
        $json["body"]["contents"][0]["contents"][1]["contents"][1]["url"] = $variant_color["image_urls"][2];
        $json["body"]["contents"][1]["contents"][0]["text"][2] = $color_name;

        //TODO: Lancrotkan Foreach

        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
