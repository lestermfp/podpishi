<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_auth extends tgHandler {

    public $_entry = [];
    public $details = [];
    public $user = [];
    public $_CFG = [];

    public function run(){

        $text = '<b>Авторизировался</b> %s (%s) (%s)';

        $this->user = db_main_users::find_by_id($this->_entry->user_id);

        $authLog = db_authLogs::find_by_id($this->details['authLogId']);

        $text = sprintf($text, $this->user->read_attribute('name') . ' ' . $this->user->read_attribute('surname'), $this->user->read_attribute('role'), $authLog->read_attribute('ip'));

        $chat_id = $_POST['botChannels']['xopbatgh'];

        if (strpos($this->user->domains, 'yabloko') !== false)
            $chat_id = $_POST['botChannels']['yabloko_chat'];

        if (strpos($this->user->domains, 'podpishi') !== false)
            $chat_id = $_POST['botChannels']['petitions_chat'];

        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text, "parse_mode" => 'HTML'));


        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();

    }

}

?>
