<?php

namespace App\Http\Library;

class Command{

    private $COMMAND = array(
        "help" => "!help",
        "new_arrival" => "!new",
        "promo" => "!promo"
    );

    public function getCommand(){
        return $this->COMMAND;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    public function isCommand($text)
    {
        return (($text[0] == '!') ? TRUE : FALSE);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//
}
