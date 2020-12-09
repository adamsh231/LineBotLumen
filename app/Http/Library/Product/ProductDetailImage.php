<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;
use App\Http\Library\Command;

class ProductDetailImage
{

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
    public function loadTemplate($event, $id)
    {
        $json = $this->templateProductDetail($id);

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'template',
                    'altText'  => 'Product Detail',
                    'template' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function loadProduct($id)
    {
        $api_product = $this->product->getWebUrlApi() . "products/" . $id;
        $api_product = $this->httpClient->get($api_product);
        $api_product = json_decode($api_product->getRawBody(), true);
        return $api_product;
    }

    private function templateProductDetail($id)
    {
        $command_postback_image_color = $this->command->getCommand()['detail_image_color'];
        $json = json_decode(file_get_contents(url('template/detail-image.json')), true);
        $api_product = $this->loadProduct($id);
        $variants = $api_product["variants"];
        $product_name = $api_product["name"];
        $product_id = $api_product["id"];
        $no_images = url('images/no-preview.jpg');
        foreach ($variants as $key => $value) {
            $json["columns"][$key] = $json["columns"][0];
            $json["columns"][$key]["imageUrl"] = is_null($value["image_urls"][0]) ? $no_images : $value["image_urls"][0];
            $json["columns"][$key]["action"]["label"] = $value["color"]["name"];
            $json["columns"][$key]["action"]["data"] = $command_postback_image_color . "=" . $key . "=" . $product_id;
            $json["columns"][$key]["action"]["displayText"] = "Color: " . $value["color"]["name"] . " for ". $product_name . ", Checking Stock..";
        }
        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
