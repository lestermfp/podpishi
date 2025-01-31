<?php

if (!isset($_CFG))
    exit('Undefined');

$command = explode(' ', $this->message['text']);
$lc_command = trim(mb_convert_case($this->message['text'], MB_CASE_LOWER, 'utf-8'));

//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => $lc_command));

if (strpos($lc_command, '/удалить') !== false){

    $appeal_id = str_replace('/удалить', '', $lc_command);
    $appeal_id = trim($appeal_id);

    if (empty($appeal_id) OR !is_numeric($appeal_id)){
        $text = 'Не указан id подписанта';
    }
    else {

        $appeal = db_appeals_list::find_by_id($appeal_id);

        if (empty($appeal)){

            $text = 'Нет такого подписанта (#' . $appeal_id . ')';

        }
        else {

            $text = "Удалён подписант " . $appeal->full_name ;

            $appeal->delete();

        }



    }


    //$text = $sum . '='. $email;

    apiRequest("sendMessage", array('chat_id' => $this->message['chat']['id'], "text" => $text));


}


if (strpos($lc_command, '/whereami') !== false){

    $text = 'Айди чята: ' . $this->message['chat']['id'];

    //apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['chainsigns_chat'], "text" => $text));
    apiRequest("sendMessage", array('chat_id' => $this->message['chat']['id'], "text" => $text));
    exit();
}

?>