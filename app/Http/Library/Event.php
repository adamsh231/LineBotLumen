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
                    'type'     => 'flex',
                    'altText'  => 'Event List',
                    'contents' => $json
                ]
            ],
        ]);
    }
    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function loadEvent()
    {
        //!! Anything Goes Wrong with the Api, Api response has Null or Something that cause an Error -> Template won't Rendering !!//
        //!! So, I make new Array for store the data first !!//
        $api_event = $this->product->getWebUrlApi() . "segments?_sort=id&_order=desc&_start=0&_end=26&is_displayed=1";

        $api_event = $this->httpClient->get($api_event);
        $api_event = json_decode($api_event->getRawBody(), true);
        return $api_event;
    }

    private function templateEvent()
    {
        $json = json_decode(file_get_contents(url('template/event.json')), true);

        $api_event = $this->loadEvent();

        //!! ----------------------- Create new Array for Store the data -------------------------- !!//
        // $data = [];
        // $key = 0;
        // foreach ($api_event as $value) {
        //     if($value["link"] != "" && !is_null($value["link"])){
        //         $data[$key]["imageUrl"] = $value["catalogs"][0]["image_large"];
        //         $data[$key]["link"] = $value["link"];
        //         $key++;
        //     }
        // }
        //!! -------------------------------------------------------------------------------------- !!//

        foreach($api_event as $key => $value){
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["body"]["contents"][0]["url"] = $value["catalogs"][0]["image_large"];
            $json["contents"][$key]["action"]["uri"] = $value["link"];
        }

        return $json;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
