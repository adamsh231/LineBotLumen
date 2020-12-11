<?php

//!! 1 -> Anything Goes Wrong with the Api, Api response has Null or Something that cause an Error -> Template won't Rendering !!//
//!! 1 ->  BEWARE OF IMAGE CAROUSEL BEHAVIOUR -> use Flex instead, its easy and custmoizable  !!//
//!! 1 ->  So, I make new Array for store the data first !!//
//!! 2 ->  Replace URL white space to %20 !!//
//!! 3 ->  Beware!, when modify template if there is any condition foreach -> {key} => value should be split !!//
//TODO: Use builder instead of json file //

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Library\User;
use App\Http\Library\Command;
use App\Http\Library\Message;
use App\Http\Library\Text;
use App\Http\Library\QuickReply;
use App\Http\Library\Product\ProductNewArrival;
use App\Http\Library\Product\ProductDetailImage;
use App\Http\Library\Product\ProductDetailImageColor;
use App\Http\Library\Promo;
use App\Http\Library\Event;
use App\Http\Library\Info;
use App\Http\Library\Brand;
use App\Http\Library\Category;

class Webhook extends Controller
{
    private $bot;
    private $request;
    private $response;
    private $httpClient;
    private $command;
    private $user;
    private $data;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->user = new User;
        $this->command = new Command;

        $this->data = $this->request->all();

        // ------------ Register If Not Registered ------------- //
        $this->registerUser($this->data);
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
        if ($event['message']['type'] == 'text') {
            $this->replyMessageTextCondition($event);
        } else {
            //TODO: reply user if not text message
        }
    }

    private function replyMessageTextCondition($event){
        $command = $this->command->getCommand();
        switch ($event['message']['text']) {
            case $command['new_arrival']:
                (new ProductNewArrival)->loadTemplate($event);
                break;
            case $command['help']:
                (new Message)->sendMessages($event, (new Text)->getHelpCommand());
                break;
            case $command['info']:
                (new Info)->loadTemplate($event);
                break;
            case $command['promo']:
                (new Promo)->loadTemplate($event);
                break;
            case $command['event']:
                (new Event)->loadTemplate($event);
                break;
            case $command['brand']:
                (new Brand)->loadTemplate($event);
                break;
            case $command['category']:
                (new Category)->loadTemplate($event);
                break;
            default:
                (new QuickReply)->loadDefaultQuickReply($event, (new Text)->getFalseCommand()[0]["text"]);
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

    private function registerUser($data)
    {
        //TODO: cant get display name -> if blocked
        foreach ($data['events'] as $event) {
            if (isset($event['source']['userId'])) $this->user->registerUser($event);
        }
    }
    //* ---------------------------------------------------------------------------------------------------------- *//
}
