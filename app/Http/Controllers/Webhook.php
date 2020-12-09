<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

use App\Http\Library\User;
use App\Http\Library\Command;


class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $data;
    private $user;

    private $WEB_URL_OFFICIAL = "https://shoesmart.co.id/";
    private $WEB_URL_API = "https://api.shoesmart.co.id/";
    private $NEW_ARRIVAL = "[5694,5297,5336,5308,5188,4507,5015,4891,5063,5027]";

    private $COMMAND = array(
        "help" => "!help",
        "new_arrival" => "!new",
        "promo" => "!promo"
    );

    public function __construct(Request $request, Response $response, User $user)
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->request = $request;
        $this->response = $response;
        $this->user = $user;
        $this->data = $request->all();

        // ------------ Register If Not Registered ------------- //
            $this->user->registerUser($this->data['events'][0]); //TODO: Why event is an array?, 0 -> first event
        // ----------------------------------------------------- //
    }

    //* ----------------------------------------- PUBLIC METHOD ------------------------------------------------- *//

    public function reply()
    {
        $data = $this->data;
        if (is_array($data['events'])) {
            foreach ($data['events'] as $event) {
                if ($event['type'] == 'message') {
                    $this->replyMessage($event);
                }
            }
        }
    }

    //* ----------------------------------------------------------------------------------------------------------- *//


    //* ----------------------------------------- PRIVATE METHOD ------------------------------------------------- *//
    private function replyMessage($event)
    {
        if ($event['message']['type'] == 'text') {
            if ((new Command)->isCommand($event['message']['text'])) {
                if ($event['message']['text'] == $this->COMMAND['new_arrival']) {
                    $this->newArrival($event);
                }
            } else {
                //TODO: reply user if not command
            }
        } else {
            //TODO: reply user if not text message
        }
    }

    private function newArrival($event)
    {
        $json = json_decode(file_get_contents(url('flex/new-arrival.json')), true);
        $product_new_arrival = $this->getProductNewArrival();

        foreach ($product_new_arrival as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["hero"]["url"] = $value["image_url"];
            $json["contents"][$key]["body"]["contents"][0]["text"] = $value["name"];
            $json["contents"][$key]["body"]["contents"][1]["contents"][0]["contents"][0]["text"] = $value["brand_name"];

            if ($value['final_price'] != $value['price']) {
                $json["contents"][$key]["body"]["contents"][2]["text"] = "Rp " . number_format($value["price"], 0, ",", ".");
                $json["contents"][$key]["body"]["contents"][2]["color"] = "#8c8c8c";
            }

            $json["contents"][$key]["body"]["contents"][3]["text"] = "Rp " . number_format($value["final_price"], 0, ",", ".");
            $json["contents"][$key]["footer"]["action"]["uri"] = $this->WEB_URL_OFFICIAL . "product/" . $value["id"] . "/0";
        }

        $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Test Flex Message',
                    'contents' => $json
                ]
            ],
        ]);
    }

    private function getProductNewArrival()
    {
        $api_new_arrival = $this->WEB_URL_API . "products?product_ids=" . $this->NEW_ARRIVAL . "&_sort=name&_order=asc&_start=0&_end=15";
        $product_new_arrival = $this->httpClient->get($api_new_arrival);
        $product_new_arrival = json_decode($product_new_arrival->getRawBody(), true);
        return $product_new_arrival;
    }

    //* ------------------------------------------------------------------------------------------------------------------- *//
}
