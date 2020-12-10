<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class Event
{
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//
    public function loadTemplate($event, $id)
    {
        $json = json_decode(file_get_contents(url('template/event.json')), true);

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
}
