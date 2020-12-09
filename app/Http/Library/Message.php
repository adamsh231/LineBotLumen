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

    public function sendMoreMessage($event)
    {
        $textMessageBuilder1 = new TextMessageBuilder('ini pesan balasan pertama');
        $textMessageBuilder2 = new TextMessageBuilder('ini pesan balasan kedua');
        $stickerMessageBuilder = new StickerMessageBuilder(1, 106);


        $multiMessageBuilder = new MultiMessageBuilder();
        $multiMessageBuilder->add($textMessageBuilder1);
        $multiMessageBuilder->add($textMessageBuilder2);
        $multiMessageBuilder->add($stickerMessageBuilder);


        $this->bot->replyMessage($event["replyToken"], $multiMessageBuilder);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
