<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_export_xslx extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $campaign = db_campaigns_list::find_by_id($this->details['arg_id']);

        $user = db_main_users::find_by_id($this->_entry->user_id);

        $text = [];

        $text[] = "❗️ <b>Запрошен экспорт в Excel</b>";
        $text[] = $campaign->title;
        $text[] = 'контактов в файле: ' . $this->details['count_export'];
        $text[] = 'запросил: ' . $user->surname . ' ' . $user->name ;

        $text = implode("\r\n", $text);

        $reply = apiRequest("sendMessage", array('chat_id' => $campaign->getLogChatId(), "parse_mode" => 'HTML', "disable_web_page_preview" => true, "text" => $text));



        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();


    }

}

?>