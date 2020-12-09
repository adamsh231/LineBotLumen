<?php

namespace App\Http\Library;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

use App\Models\User as UserModel;

class User{

    private $id;
    private $name;

    private $bot;
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
        $this->bot  = new LINEBot($this->httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getName(){
        return $this->name;
    }

    //* --------------------------------------- MODIFIER PUBLIC PROPERTY ----------------------------------------------- *//

    public function registerUser($event)
    {
        $data["line_id"] = $event['source']['userId'];
        $data["name"] = $this->getDisplayName($data["line_id"]);
        (new UserModel)->register($data);
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

    //* --------------------------------------- MODIFIER PRIVATE PROPERTY ---------------------------------------------- *//

    private function getDisplayName($line_id)
    {
        $getprofile = $this->bot->getProfile($line_id);
        $profile = $getprofile->getJSONDecodedBody();
        $this->setName($profile['displayName']);
        return $this->getName();
    }

    //* ---------------------------------------------------------------------------------------------------------------- *//

}
