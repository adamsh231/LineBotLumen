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
use App\Http\Library\Product\ProductNewArrival;
use App\Http\Library\Product\ProductDetailImage;

class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $command;
    private $user;
    private $data;

    public function __construct(Request $request, Response $response, User $user, Command $command)
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->request = new Request;
        $this->response = new Response;
        $this->user = new User;
        $this->command = new Command;

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
            if ($this->command->isCommand($event['message']['text'])) {
                if ($event['message']['text'] == $this->command->getCommand()['new_arrival']) {
                    (new ProductNewArrival)->loadTemplate($event);
                }else if($event['message']['text'] == $this->command->getCommand()['detail_image']){
                    (new ProductDetailImage)->loadTemplate($event);
                }
            } else {
                //TODO: reply user if not command
            }
        } else {
            //TODO: reply user if not text message
        }
    }
    //* ---------------------------------------------------------------------------------------------------------- *//
}
