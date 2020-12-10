<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;

class Message
{
    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    public function sendMessages($event, $arr_text)
    {
        $messageBuilder = [];
        $multiMessageBuilder = new MultiMessageBuilder();
        foreach ($arr_text as $key => $value) {
            if ($value["type"] == "text") {
                $text = $this->detectEmoji($value["text"]);
                $messageBuilder[$key] = new TextMessageBuilder($text);
            } else {
                //TODO: Define Soon! Sticker
            }
            $multiMessageBuilder->add($messageBuilder[$key]);
        }
        $this->bot->replyMessage($event["replyToken"], $multiMessageBuilder);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    private function decodeEmoji($emoji)
    {
        $code = str_replace('0x', '', $emoji); //!! Improve with Regex if Possible !!//
        $bin = hex2bin(str_repeat('0', 8 - strlen($code)) . $code);
        $emoji =  mb_convert_encoding($bin, 'UTF-8', 'UTF-32BE');
        return $emoji;
    }

    private function detectEmoji($text)
    {
        $split = explode(' ', $text);
        foreach ($split as $key => $value) {
            if ($value[0] == '0' && $value[1] == 'x') {
                $split[$key] = $this->decodeEmoji($value);
            }
        }
        $merged = implode(" ",$split);
        return $merged;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
