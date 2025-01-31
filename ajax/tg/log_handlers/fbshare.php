<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_fbshare extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $text = "🔈 Случился share (%s)\r\n%s\r\n%s\r\n%s";

        $text = sprintf($text, $this->details['social'], $this->details['full_name'] . ' (' . $this->details['city'] . ')', $this->details['phone'], $this->details['email']);

        //print $text ;

        //exit();

        $reply = apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['petitions_chat'], "parse_mode" => 'HTML', "disable_web_page_preview" => true, "text" => $text));



        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();


    }

}

?>