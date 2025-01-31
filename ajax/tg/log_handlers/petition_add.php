<?php

if (!class_exists('tgHandler'))
    exit('Undefined');

class tgHandler_petition_add extends tgHandler {

    public $_entry = [];
    public $details = [];

    public function run(){

        $user = db_old_appeals::find_by_id($this->_entry->user_id);

        if (empty($user)){
            $this->_entry->tg_bot_status = 'sent';
            $this->_entry->save();
            return;
        }

        $hours = $user->read_attribute('reg_date')->format('H');

        //print '<pre>' . print_r($user, true) . '</pre>';
        //exit();

        if ($hours >= 21 OR $hours < 9){

            $extra_hours = 0 ;
            if ($hours >= 20)
                $extra_hours = 5 * 3600 ;

            $user->is_night = 1 ;
            $user->queue_date = date('Y-m-d 12:00:00', $user->read_attribute('reg_date')->format('U') + $extra_hours );
            $user->save();
        }



        $address_text = $user->read_attribute('city') . ', ' . $user->read_attribute('street_name') . ' ' . $user->read_attribute('house_number') . ' кв. ' . $user->read_attribute('appartment');

        $petition_text = 'Текст стандартный.';
        $text = "Стандартная петиция \r\n ФИО: %s (%s) \r\n Адрес: %s \r\n ID петици: %s \r\n";

        if ($user->read_attribute('is_night') == 1)
            $text = str_replace('Стандартная петиция', 'Стандартная петиция НОЧНАЯ', $text);

        if ($user->read_attribute('is_manual_text') == 1){
            $petition_text = "" . '===> ' . $user->read_attribute('petition_text');

            $text = "Новая петиция \r\n ФИО: %s (%s) \r\n Адрес: %s \r\n Телефон: %s \r\n ID петици: %s \r\n Район: %s \r\n %s";

            if ($user->read_attribute('is_night') == 1)
                $text = str_replace('Новая петиция', 'Новая петиция НОЧНАЯ', $text);

            $text = sprintf($text, $user->read_attribute('name'), $user->read_attribute('destination'),$address_text, $user->read_attribute('phone'), $user->read_attribute('id'), $user->read_attribute('district_name'), $petition_text);
        }
        else {
            $text = sprintf($text, $user->read_attribute('name'), $user->read_attribute('destination'),$address_text, $user->read_attribute('id'));
        }



        apiRequest("sendMessage", array('chat_id' => '-130938339', "text" => $text));


        $this->_entry->tg_bot_status = 'sent';
        $this->_entry->save();

    }

}

?>