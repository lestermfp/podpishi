<?php
include('./tg_botBase.php');

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);



class gudkovBot {
	
	public $is_ready = false;
	public $act = '';
	public $message = '';
	private $chat_id = 0;
	private $allowed_cats = array('message', 'callback_query');
	
	/*
		Ищем к какому аккаунту привязан написавший боту человек
	*/
    public function __construct($message) {
		
		
		$this->initVars($message);

        $this->checkIfInvitedToNewChat();
		
		if (!$this->checkAuth())
			return ;

		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Не' . json_encode($this->user->to_array())));

		$this->processMessage($message);

		return ;

		

	}

    private function checkIfInvitedToNewChat(){

        if (!isset($this->message['new_chat_member']) OR $this->message['new_chat_member']['id'] != $_POST['bot']['id'])
            return ;

        $text = 'Я теперь есть в чате «%s» (%s)';

        $text = sprintf($text, $this->message['chat']['title'], $this->message['chat']['id']);

        apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => $text));

    }
	
	private function percentsOf($a, $b){
		
		return round(($a / $b) * 100);
	}

	
	private function processMessage($message){
		Global $_CFG ;

        include('./tg_botCommands.php');

		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Вы написали: "' . $message['text'] . '"'));
	}
	
	
	private function initVars($message){
		
		next($message);
		$this->act = key($message);
		
		if (!in_array($this->act, $this->allowed_cats)){
			apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Не найден act ' . $this->act));
			exit();
		}
		
		
		
		$message = $message[$this->act] ;
		
		$this->message = $message ;
		if (isset($message['chat']))
			$this->chat_id = $message['chat']['id'];
		
		$this->from_id = $message['from']['id'];
				
		$this->initMemcache();
		
	}
	
	private function initMemcache(){
		$this->memcache = new Memcache;
		$this->memcache->connect('127.0.0.1', 11211) or die ("Could not connect");			
	}

	
	private function checkAuth(){

		$allowedTelegram = array('159147853', '344702', '1223639', '221693', '52376382', '1834816');
		
		if (!in_array($this->from_id, $allowedTelegram))
			return false;
		
		
		//$this->user = db_user::find_by_login('xopbatgh');
		
		return true;
		
	}


	
}

$gudkovBot = array();

function processTgQuery($message){


	
	
	$gudkovBot = new gudkovBot($message);
	
	if ($gudkovBot->is_ready == false)
		return ;

}


define('WEBHOOK_URL', 'https://podpishi.org/ajax/tg/tg_pdBot.php');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$chat_id = $_POST['botChannels']['my'];
$chat_id = $_POST['botChannels']['main_chat'];


$content = file_get_contents("php://input");
$update = json_decode($content, true);
file_put_contents('./tg_update', serialize($update));


if (!$update) {
  // receive wrong update, must not happen
  exit;
}



if (isset($update["message"]) OR isset($update["callback_query"])) {
	
	if (empty($chat_id)){
		apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => json_encode($update)))	;
		exit();
	}
	else {
		
		processTgQuery($update);
	}
	
}
