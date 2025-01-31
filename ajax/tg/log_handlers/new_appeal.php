<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_new_appeal extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $text = "✍️ %s\r\n%s 
%s %s 
id%s (подписантов: %s)";

        $abbr = '';

        $campaign = db_campaigns_list::find_by_id($this->details['subarg_id']);

        $appeal = db_appeals_list::find_by_id($this->details['arg_id']);

        if (empty($appeal)){
            $this->_entry->tg_bot_status = 'none';
            $this->_entry->save();
            return;
        }

        $amount = $campaign->getAppealsAmount();

        $url = '<a href="' . $campaign->getAbsUrl() . '">' . $campaign->title . '</a>';

        $text = sprintf($text, $url,$appeal->full_name . $abbr, formatPhone($appeal->phone), $appeal->email, $appeal->id, $amount);

        if ($campaign->domain == 'yabloko'){

            $text .= "\r\n" . $appeal->getAddressCity();

        }

        if ($campaign->id == 33){

            $pPNg = [$appeal->ng_apple_role];

            if ($appeal->ng_apple_city != '')
                $pPNg[] = $appeal->ng_apple_city;

            if ($appeal->ng_apple_year != '')
                $pPNg[] = $appeal->ng_apple_year;

            $text .= "\r\n" . implode(', ', $pPNg);

            $text = '🍏' . $text ;

        }

        if ($campaign->id == 34){

            $pPNg = [$appeal->region_name];

            $text .= "\r\n" . implode(', ', $pPNg);

            $text = '♒️' . $text ;

        }

        $reply = apiRequest("sendMessage", array('chat_id' => $campaign->getLogChatId(), "parse_mode" => 'HTML', "disable_web_page_preview" => true, "text" => $text));



        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();


    }

}

?>