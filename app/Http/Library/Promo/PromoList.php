<?php

namespace App\Http\Library\Promo;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Promo\Promo;

class PromoList
{
    private $promo;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->promo = new Promo;
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
        $api_promo = $this->promo->getWebUrlApi() . "promonew/query?is_active=1&is_displayed=1";
        $api_promo = $this->httpClient->get($api_promo);
        $api_promo = json_decode($api_promo->getRawBody(), true);
        return $api_promo;
    }

    private function templatePromoList(){
        $json = json_decode(file_get_contents(url('template/promo-list.json')), true);
        $api_promo = $this->loadPromo();
        foreach ($api_promo as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["hero"]["url"] = $value["image_square"];
            $json["contents"][$key]["body"]["contents"][0]["text"] = $value["name"];
            $json["contents"][$key]["body"]["contents"][1]["text"] = $value["description_short"];
            $json["contents"][$key]["footer"]["contents"][1]["contents"][0]["contents"][1]["text"] = "Coming Soon!";
            $json["contents"][$key]["footer"]["contents"][1]["contents"][1]["contents"][0]["data"] = "PostBack!";
            $json["contents"][$key]["footer"]["contents"][1]["contents"][1]["contents"][0]["displayText"] = "Text Post Back";
        }
        return $json;
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

}
