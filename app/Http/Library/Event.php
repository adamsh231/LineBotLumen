<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\Product\Product;

class Event
{
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->product = new Product;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event)
    {
        $json = $this->templateEvent();

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'template',
                    'altText'  => 'Event List',
                    'template' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function loadEvent()
    {
        // $api_event = $this->product->getWebUrlApi() . "segments?_sort=id&_order=desc&_start=0&_end=26&is_displayed=1";
        $api_event = $this->product->getWebUrlApi() . "promonew/query?is_active=1&is_displayed=1";
        $api_event = $this->httpClient->get($api_event);
        $api_event = json_decode($api_event->getRawBody(), true);
        return $api_event;
    }

    private function templateEvent()
    {
        $json = json_decode(file_get_contents(url('template/event.json')), true);

        $api_event = $this->loadEvent();
        foreach ($api_event as $key => $value) {
            $json["columns"][$key]["action"]["label"] = $value["name"];
            // if (!is_null($value["link"])) {
            //     $json["columns"][$key] = $json["columns"][0];
            //     // $json["columns"][$key]["imageUrl"] = $value["catalogs"][0]["image_large"];
            //     $json["columns"][$key]["action"]["label"] = $value["name"];
            //     $json["columns"][$key]["action"]["uri"] = $value["link"];
            //     $key++;
            // }
        }
        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
