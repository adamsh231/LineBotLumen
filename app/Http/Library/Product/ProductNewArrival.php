<?php

namespace App\Http\Library\Product;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;
use App\Http\Library\Command;

class ProductNewArrival{

    private $product;
    private $command;

    private $NEW_ARRIVAL = "[5694,5297,5336,5308,5188,4507,5015,4891,5063,5027]";

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->product = new Product;
        $this->command = new Command;
    }

    public function getListNewArrival(){
        return $this->NEW_ARRIVAL;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    public function loadTemplate($event){
        $json = $this->templateNewArrival();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'New Arrival Product',
                    'contents' => $json
                ]
            ],
        ]);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function loadProduct()
    {
        $api_product = $this->product->getWebUrlApi() . "products?product_ids=" . $this->NEW_ARRIVAL . "&_sort=name&_order=asc&_start=0&_end=15";
        $api_product = $this->httpClient->get($api_product);
        $api_product = json_decode($api_product->getRawBody(), true);
        return $api_product;
    }

    private function templateNewArrival(){
        $command_postback_image = $this->command->getCommand()['detail_image'];
        $json = json_decode(file_get_contents(url('template/new-arrival.json')), true);
        $api_product = $this->loadProduct();
        $no_images = url('images/no-preview.jpg');
        foreach ($api_product as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["hero"]["url"] = is_null($value["image_url"]) ?  $no_images : $value["image_url"];
            $json["contents"][$key]["hero"]["action"]["data"] = $command_postback_image ."=". $value["id"];
            $json["contents"][$key]["hero"]["action"]["displayText"] = "Color List: ".$value["name"];
            $json["contents"][$key]["body"]["contents"][0]["text"] = $value["name"];
            $json["contents"][$key]["body"]["contents"][1]["contents"][0]["contents"][0]["text"] = $value["brand_name"];

            if ($value['final_price'] != $value['price']) {
                $json["contents"][$key]["body"]["contents"][2]["text"] = "Rp " . number_format($value["price"], 0, ",", ".");
                $json["contents"][$key]["body"]["contents"][2]["color"] = "#8c8c8c";
            }

            $json["contents"][$key]["body"]["contents"][3]["text"] = "Rp " . number_format($value["final_price"], 0, ",", ".");
            $json["contents"][$key]["footer"]["action"]["uri"] = $this->product->getWebUrlOfficial() . "product/" . $value["id"] . "/0";
        }
        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
