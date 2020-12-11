<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class Category
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
        $json = $this->templateCategory();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'List Category',
                    'contents' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//
    private function loadCategory()
    {
        $api_category = $this->product->getWebUrlApi() . "categories_group?_sort=name&_order=asc&_start=0&_end=30&gender=female";
        $api_category = $this->httpClient->get($api_category);
        $api_category = json_decode($api_category->getRawBody(), true);
        return $api_category;
    }

    private function templateCategory()
    {
        $json = json_decode(file_get_contents(url('template/category.json')), true);
        $api_category = $this->loadCategory();

        foreach ($api_category as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["body"]["contents"][0]["url"] = $value["icon_url"];
            $json["contents"][$key]["body"]["contents"][0]["action"]["uri"] = $this->product->getWebUrlOfficial() . "products?categories=" . $value["name"];
        }

        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
