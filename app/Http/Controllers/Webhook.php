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
    private $WEB_URL = "https://shoesmartlinebot.herokuapp.com/";
    private $RESULT_DEFAULT_MESSAGE = "Unknown Events!";
    private $DEFAULT_GREETINGS = "Assalamu'alaikum!";


    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // create bot object
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

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

    private function replySingleUser($event)
    {
        // ------------ Register If Not Registered ------------- //
        $register = $this->registerUser($event);
        // ----------------------------------------------------- //
        $result = $this->RESULT_DEFAULT_MESSAGE;
        if ($event['message']['type'] == 'text') {
            $greetings = $this->DEFAULT_GREETINGS;
            if (strtolower($event['message']['text']) == 'flex message') {
                $result = $this->replyFlexMessage($event);
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

    private function replyFlexMessage($event ,$url = "")
    {
        $flexTemplate = file_get_contents(url('flex/flex-message.json'));
        $result = $this->httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
            'replyToken' => $event['replyToken'],
            'messages'   => [
                [
                    'type'     => 'flex',
                    'altText'  => 'Test Flex Message',
                    'contents' => json_decode($flexTemplate)
                ]
            ],
        ]);
        return $result;
    }

    private function registerUser($event){
        $data["line_id"] = $event['source']['userId'];
        $data["name"] = $this->getDisplayName($data["line_id"]);
        $user = (new User)->register($data);
        return $user;
    }

    private function getDisplayName($line_id){
        $display_name = "";
        $getprofile = $this->bot->getProfile($line_id);
        $profile = $getprofile->getJSONDecodedBody();
        $display_name = $profile['displayName'];
        return $display_name;
    }
}
