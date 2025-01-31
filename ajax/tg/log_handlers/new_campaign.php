<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_new_campaign extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $text = "Новая кампания\r\n%s";

        $campaign = db_campaigns_list::find_by_id($this->details['arg_id']);

        if (!empty($campaign)){


            $url = '<a href="' . $campaign->getAbsUrl() . '">' . $campaign->title . '</a>';

            $text = sprintf($text, $url);

            //print $text ;

            //exit();

            $reply = apiRequest("sendMessage", array('chat_id' => $campaign->getLogChatId(), "parse_mode" => 'HTML', "disable_web_page_preview" => true, "text" => $text));



            
        }

        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();


    }

}

?>