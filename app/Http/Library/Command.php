<?php

namespace App\Http\Library;

class Command
{

    private $COMMAND = array(
        "new_arrival" => "!new",
        "detail_image" => "!image",
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
        return $split;
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
