<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_new_user extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $text = "Новый аккаунт: %s (+%s)";


        $user = db_main_users::find_by_id($this->details['arg_id']);
        $text = sprintf($text, $user->name . ' ' . $user->surname, $user->phone);
        $text .= "с сайта \r\n<b>" . $user->oauth_from . "</b>";

        //print $text ;

        //exit();

        $reply = apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['petitions_chat'], "parse_mode" => 'HTML', "disable_web_page_preview" => true, "text" => $text));


        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();

    }

}

?>