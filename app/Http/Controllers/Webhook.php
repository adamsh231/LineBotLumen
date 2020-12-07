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

class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $WEB_URL = "https://shoesmartlinebot.herokuapp.com/";


    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        // create bot object
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    public function index()
    {
        // -------------------------------------Reply Messages------------------------------------- //
        // TODO: Reply Sticker, More Messages or Images
        $data = $this->request->all();
        if (is_array($data['events'])) {
            foreach ($data['events'] as $event) {
                if ($event['type'] == 'message') {
                    if (
                        $event['source']['type'] == 'group' or
                        $event['source']['type'] == 'room'
                    ) {
                        //! Message From Group !//
                        if ($event['source']['userId']) {

                            $userId = $event['source']['userId'];
                            $getprofile = $this->bot->getProfile($userId);
                            $profile = $getprofile->getJSONDecodedBody();
                            $greetings = new TextMessageBuilder("Halo, " . $profile['displayName']);

                            $result = $this->bot->replyMessage($event['replyToken'], $greetings); //* Result *//
                        }
                    } else {
                        //! Message From Single User !//
                        if ($event['message']['type'] == 'text') {

                            $text = "Assalamu'alaikum!";

                            if (strtolower($event['message']['text']) == 'user id') {
                                $result = $this->bot->replyText($event['replyToken'], $event['source']['userId']);
                            } elseif (strtolower($event['message']['text']) == 'flex message') {

                                $flexTemplate = file_get_contents("../flex_message.json"); // template flex message
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
                            } else {
                                $result = $this->bot->replyText($event['replyToken'], $text); //* Result *//
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
                            ); //* Result *//
                        }
                    }
                }
            }
        }
        // ----------------------------------------------------------------------------------------- //
    }
}
