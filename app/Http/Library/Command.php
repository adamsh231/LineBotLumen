<?php

namespace App\Http\Library;

class Command{
    public function isCommand($text)
    {
        return (($text[0] == '!') ? TRUE : FALSE);
    }
}
