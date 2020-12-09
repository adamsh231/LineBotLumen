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

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    private function setHelpCommand()
    {
        $help = "Command List" . PHP_EOL
            . "'!help' : Melihat list command" . PHP_EOL
            . "'!info' : Informasi Tentang Shoesmart" . PHP_EOL
            . "'!new'  : Menampilkan Produk New Arrival" . PHP_EOL
            . "'!promo': Menampilkan Promo Terbaru";
        $this->help_command[0] = array("type" => "text", "text" => $help);
    }

    private function setFalseCommand()
    {
        $false = "Perintah tidak diketahui." . PHP_EOL . "ketik '!help' untuk melihat daftar command";
        $this->false_command[0] = array("type" => "text", "text" => $false);
    }

    private function setInfoCommand()
    {
        $intro = "Shoesmart adalah Start up E-commerce khusus sepatu wanita dan pria" . PHP_EOL .
                "yang didirikan pada 2016. Kami hadir sebagai jawaban atas tantangan dunia". PHP_EOL .
                "teknologi yang semakin maju, termasuk dalam hal berbelanja secara online.". PHP_EOL .
                "Sebagai platform online yang mempertemukan antara pebisnis sepatu lokal dan". PHP_EOL .
                "para pembelinya, Shoesmart menyediakan berbagai macam produk sepatu terkini". PHP_EOL .
                "berkualitas dengan harga terjangkau keluaran berbagai brand lokal terbaik di". PHP_EOL .
                "Indonesia.";
        $this->info_command[0] = array("type" => "text", "text" => $intro);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
