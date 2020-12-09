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

    public function getHelpCommand(){
        return $this->help_command;
    }

    public function getInfoCommand(){
        return $this->info_command;
    }

    public function getFalseCommand(){
        return $this->false_command;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    private function setHelpCommand()
    {
        $help = "Command List \n
                '!help' : Melihat list command \n
                '!info' : Informasi Tentang Shoesmart\n
                '!new'  : Menampilkan Produk New Arrival\n
                '!promo': Menampilkan Promo Terbaru";
        $this->help_command[0] = array("type" => "text", "text" => $help);
    }

    private function setFalseCommand()
    {
        $false = "Perintah tidak diketahui, Silahkan ketik '!help' untuk melihat daftar command";
        $this->false_command[0] = array("type" => "text", "text" => $false);
    }

    private function setInfoCommand()
    {
        $intro = "Shoesmart adalah Start up E-commerce khusus sepatu wanita dan pria
                yang didirikan pada 2016. Kami hadir sebagai jawaban atas tantangan dunia
                teknologi yang semakin maju, termasuk dalam hal berbelanja secara online.
                Sebagai platform online yang mempertemukan antara pebisnis sepatu lokal dan
                para pembelinya, Shoesmart menyediakan berbagai macam produk sepatu terkini
                berkualitas dengan harga terjangkau keluaran berbagai brand lokal terbaik di
                Indonesia.";
        $this->info_command[0] = array("type" => "text", "text" => $intro);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}