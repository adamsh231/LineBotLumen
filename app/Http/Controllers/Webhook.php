<?php

namespace App\Http\Controllers;

use App\Gateway\EventLogGateway;
use App\Gateway\QuestionGateway;
use App\Gateway\UserGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

use App\Models\User;
use App\Models\Link;

class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $flex_message_content;

    private $WEB_URL = "https://shoesmartlinebot.herokuapp.com/";
    private $WEB_URL_OFFICIAL = "https://shoesmart.co.id/";
    private $RESULT_DEFAULT_MESSAGE = "Unknown Events!";
    private $DEFAULT_GREETINGS = "Assalamu'alaikum!";
    private $WEB_URL_API = "https://api.shoesmart.co.id/";
    private $NEW_ARRIVAL = "[5694,5297,5336,5308,5188,4507]";
    // private $NEW_ARRIVAL = "[5694,5297,5336,5308,5188,4507,5015,4891,5063,5027,5122,5149]";   //update 18/11/2020

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->flex_message_content = json_decode(file_get_contents(url('flex/flex-message.json')), true);

        // create bot object
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    //* ----------------------------------------- PUBLIC METHOD ------------------------------------------------- *//

    // TODO: Reply Sticker, More Messages or Images
    public function reply()
    {
        $result = $this->RESULT_DEFAULT_MESSAGE;
        $data = $this->request->all();
        if (is_array($data['events'])) {
            foreach ($data['events'] as $event) {
                if ($event['type'] == 'message') {
                    if ($event['source']['type'] == 'group' or $event['source']['type'] == 'room') {
                        $result = $this->replyGroupOrRoom($event);
                    } else {
                        $result = $this->replySingleUser($event);
                    }
                }
            }
            $result = $this->response->setStatusCode($result->getHTTPStatus());
            return $result;
        } else {
            return $result;
        }
    }

    public function getContent($message_id)
    {
        $result = $this->bot->getMessageContent($message_id);
        $response = $this->response
            ->setContent($result->getRawBody())
            ->header('Content-Type', $result->getHeader('Content-Type'));
        return $response;
    }

    public function pushMessage()
    {
        //! -----------------@Line Developers----------------- !//
        $userId = 'Ub383312db4a3d9ec89d2afedbac32e24'; // My Account
        //! -------------------------------------------------- !//

        $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
        $result = $this->bot->pushMessage($userId, $textMessageBuilder);

        $response = $this->response
            ->setContent("Pesan push berhasil dikirim!")
            ->header('Content-Type', 'application/json')
            ->setStatusCode($result->getHTTPStatus());

        return $response;
    }

    public function pushMessageBroadcast()
    {
        //! -----------------@Line Developers----------------- !//
        $userList = [
            'Ub383312db4a3d9ec89d2afedbac32e24', // My Account
            //! Apps can run if User Id is valid !//
        ];
        //! -------------------------------------------------- !//

        $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
        $result = $this->bot->multicast($userList, $textMessageBuilder);
        $response = $this->response
            ->setContent("Pesan push berhasil dikirim!")
            ->header('Content-Type', 'application/json')
            ->setStatusCode($result->getHTTPStatus());

        return $response;
    }

    public function getProfile()
    {
        //! -----------------@Line Developers----------------- !//
        $userId = 'Ub383312db4a3d9ec89d2afedbac32e24'; // My Account
        //! -------------------------------------------------- !//
        $result = $this->bot->getProfile($userId);
        $response = $this->response
            ->setContent(json_encode($result->getJSONDecodedBody()))
            ->header('Content-Type', 'application/json')
            ->setStatusCode($result->getHTTPStatus());
    }
    //* ----------------------------------------------------------------------------------------------------------- *//


    //* ----------------------------------------- PRIVATE METHOD ------------------------------------------------- *//

    private function replySingleUser($event)
    {
        // ------------ Register If Not Registered ------------- //
        $register = $this->registerUser($event);
        // ----------------------------------------------------- //
        $result = $this->RESULT_DEFAULT_MESSAGE;
        if ($event['message']['type'] == 'text') {
            $greetings = $this->DEFAULT_GREETINGS;
            if (strtolower($event['message']['text']) == 'new') {
                $result = $this->flexListNewArrival($event);
            } else {
                $result = $this->bot->replyText($event['replyToken'], $greetings);
            }
        } elseif (
            $event['message']['type'] == 'image' or
            $event['message']['type'] == 'video' or
            $event['message']['type'] == 'audio' or
            $event['message']['type'] == 'file'
        ) {
            $contentURL =  $this->WEB_URL . "content/" . $event['message']['id'];
            $contentType = ucfirst($event['message']['type']);
            $result = $this->bot->replyText(
                $event['replyToken'],
                $contentType . " yang Anda kirim bisa diakses dari link:\n " . $contentURL
            );
        }
        return $result;
    }

    //TODO: Under Development Soon!
    private function replyGroupOrRoom($event)
    {
        $result = $this->RESULT_DEFAULT_MESSAGE;
        if ($event['source']['userId']) {
            $userId = $event['source']['userId'];
            $displayName = $this->getDisplayName($userId);
            $greetings = new TextMessageBuilder("Halo, " . $displayName);
            $result = $this->bot->replyMessage($event['replyToken'], $greetings);
        }
        return $result;
    }

    private function replyFlexMessage($event, $json)
    {
        $result = $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Test Flex Message',
                    'contents' => $json
                ]
            ],
        ]);
        return $result;
    }

    private function registerUser($event)
    {
        $data["line_id"] = $event['source']['userId'];
        $data["name"] = $this->getDisplayName($data["line_id"]);
        $user = (new User)->register($data);
        return $user;
    }

    private function getDisplayName($line_id)
    {
        $display_name = "";
        $getprofile = $this->bot->getProfile($line_id);
        $profile = $getprofile->getJSONDecodedBody();
        $display_name = $profile['displayName'];
        return $display_name;
    }

    private function getProductNewArrival()
    {
        $api_new_arrival = $this->WEB_URL_API . "products?product_ids=" . $this->NEW_ARRIVAL . "&_sort=name&_order=asc&_start=0&_end=15";
        $product_new_arrival = $this->httpClient->get($api_new_arrival); //TODO: Line Library used -> Changed Facades soon !//
        $product_new_arrival = json_decode($product_new_arrival->getRawBody(), true);
        return $product_new_arrival;
    }

    private function flexListNewArrival($event)
    {
        $json = $this->flex_message_content;
        $product_new_arrival = $this->getProductNewArrival();
        foreach ($product_new_arrival as $key => $value) {
            $json["contents"][$key] = $json["contents"][0];
            $json["contents"][$key]["hero"]["url"] = $value["image_url"];
            $json["contents"][$key]["body"]["contents"][0]["text"] = $value["name"];
            $json["contents"][$key]["body"]["contents"][1]["contents"][0]["contents"][0]["text"] = $value["brand_name"];
            $json["contents"][$key]["body"]["contents"][2]["text"] = "Rp " . number_format($value["price"], 0, ",", ".");
            $json["contents"][$key]["body"]["contents"][3]["text"] = "Rp " . number_format($value["final_price"], 0, ",", ".");
            $json["contents"][$key]["footer"]["contents"][0]["action"]["uri"] = $this->WEB_URL_OFFICIAL . "product/" . $value["id"] . "/0";
        }
        return $this->replyFlexMessage($event, $json);
    }

    //* ------------------------------------------------------------------------------------------------------------------- *//
}
