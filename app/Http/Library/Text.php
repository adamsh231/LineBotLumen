<?php

namespace App\Http\Library;

class Text
{
    private $help_command;
    private $false_command;

    public function getHelpCommand()
    {
        $this->help_command = json_decode(file_get_contents(url('message/help.json')), true);
        return $this->help_command;
    }

    public function getFalseCommand()
    {
        $this->false_command = json_decode(file_get_contents(url('message/false.json')), true);
        return $this->false_command;
    }
}
