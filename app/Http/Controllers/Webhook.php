<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Http\Library\User;
use App\Http\Library\Command;
use App\Http\Library\Message;
use App\Http\Library\Text;
use App\Http\Library\QuickReply;
use App\Http\Library\Product\ProductNewArrival;
use App\Http\Library\Product\ProductDetailImage;
use App\Http\Library\Product\ProductDetailImageColor;
use App\Http\Library\Promo\PromoList;

class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $command;
    private $user;
    private $data;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

        $this->request = new Request;
        $this->response = new Response;
        $this->user = new User;
        $this->command = new Command;

        $this->data = $this->request->all();

        // ------------ Register If Not Registered ------------- //
        $this->user->registerUser($this->data['events'][0]); //TODO: Event is an array | Change follow event soon!
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
                } else if ($event['type'] == 'postback') {
                    $this->replyPostBack($event);
                }
            }
        }
    }

    //* ----------------------------------------------------------------------------------------------------------- *//


    //* ----------------------------------------- PRIVATE METHOD ------------------------------------------------- *//
    private function replyMessage($event)
    {
        $command = $this->command->getCommand();
        if ($event['message']['type'] == 'text') {
            if ($this->command->isCommand($event['message']['text'])) {
                if ($event['message']['text'] == $command['new_arrival']) {
                    (new ProductNewArrival)->loadTemplate($event);
                } else if ($event['message']['text'] == $command['help']) {
                    (new Message)->sendMessages($event, (new Text)->getHelpCommand());
                } else if ($event['message']['text'] == $command['info']) {
                    (new Message)->sendMessages($event, (new Text)->getInfoCommand());
                } else if ($event['message']['text'] == $command['promo']) {
                    (new PromoList)->loadTemplate($event);
                } else {
                    (new QuickReply)->loadDefaultQuickReply($event, (new Text)->getFalseCommand()[0]["text"]);
                }
            } else {
                (new QuickReply)->loadDefaultQuickReply($event, (new Text)->getFalseCommand()[0]["text"]);
            }
        } else {
            //TODO: reply user if not text message
        }
    }

    private function replyPostBack($event)
    {
        $command = $this->command->getCommand();
        $event_command = $this->command->splitCommand($event['postback']['data']);
        if ($event_command['command'] == $command['detail_image']) {
            (new ProductDetailImage)->loadTemplate($event, $event_command['data']);
        } else if ($event_command['command'] == $command['detail_image_color']) {
            (new ProductDetailImageColor)->loadTemplate($event, $event_command['data'], $event_command[2]);
        }
    }
    //* ---------------------------------------------------------------------------------------------------------- *//
}
