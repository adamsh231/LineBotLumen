<?php

namespace App\Http\Library;

class Command
{
    //! Do not change Association key name, Will cause error in many classes !//
    private $COMMAND = array(
        "new_arrival" => "!new",
        "help" => "!help",
        "info" => "!info",
        "promo" => "!promo",
        "event" => "!event",

        // ---- Only Postback ----- //
        "detail_image" => "!image",
        "detail_image_color" => "!image_color",
        // ------------------- //
    );

    public function getCommand()
    {
        return $this->COMMAND;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    public function isCommand($text)
    {
        return (($text[0] == '!') ? TRUE : FALSE);
    }

    public function splitCommand($text)
    {
        $splitter = explode("=", $text);
        $split["command"] = $splitter[0];
        $split["data"] = $splitter[1];
        foreach ($splitter as $key => $value) {
            $split[$key] = $value;
        }
        return $split;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
