<?php

namespace App\Http\Library;

class Text
{
    private $info_command;
    private $help_command;
    private $false_command;

    public function __construct()
    {
        $this->setHelpCommand();
        $this->setInfoCommand();
        $this->setFalseCommand();
    }

    public function getHelpCommand()
    {
        return $this->help_command;
    }

    public function getInfoCommand()
    {
        return $this->info_command;
    }

    public function getFalseCommand()
    {
        return $this->false_command;
    }

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ----------------------------------------------- *//

    private function setHelpCommand()
    {
        $this->help_command = json_decode(file_get_contents(url('message/help.json')), true);
    }

    private function setFalseCommand()
    {
        $this->false_command = json_decode(file_get_contents(url('message/false.json')), true);
    }

    private function setInfoCommand()
    {
        $this->info_command = json_decode(file_get_contents(url('message/info.json')), true);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
