<?php
//include('../../config.php');

	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
	
if (!isset($_GET['debug'])){
	//print 'debug';
	//exit();
}

//file_put_contents('./get_tg', serialize($_GET));
//file_put_contents('./post_tg', serialize($_POST));	

$_POST['botChannels'] = array(
	'my' => '159147853',
	'kirill' => '28106',
	'main_chat' => '-177883810',
);

		
define('BOT_TOKEN', '326386592:AAET9ityN-zZRKbQuE-k3i1l7s4rUfOnP_U');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  header("Content-Type: application/json");
  echo json_encode($parameters);
  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successfull: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);
  
  //print 'Url is ' . $url . '<br />';

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}

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

		
		
		if (!$this->checkAuth())
			return ;
		
	
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Не' . json_encode($this->user->to_array())));
		
		
		//if ($this->isThereCallbackQuery())
			//return ;
		
		
	
	

		$this->processMessage($message);

		return ;

		

	}
	
	private function percentsOf($a, $b){
		
		return round(($a / $b) * 100);
	}
	
	private function isThereCallbackQuery(){
		
		$callback_active = $this->memcache->get('callback_active_' . $this->from_id);
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'received ' . $callback_active));
		
		$isThereCallbackQuery = false ;
		
		if ($this->act == 'callback_query'){
			$this->processCallbackQueryInit();
			$isThereCallbackQuery = true;
		}
		
		if ($callback_active !== false AND $isThereCallbackQuery == false){
			$this->message['data'] = json_decode($callback_active, true);
			$this->processCallbackQueryEngine();
			$isThereCallbackQuery = true;
		}	
		
		//save results
		
		if ($isThereCallbackQuery){
			//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'received ' . json_encode($this->message['data'])));
			
			$result = $this->memcache->set('callback_active_' . $this->from_id, json_encode($this->message['data']), MEMCACHE_COMPRESSED, 60);
		}
		
		
		return $isThereCallbackQuery;
	}
	
	private function processMessage($message){
		Global $_CFG ;
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Привет!' . "\r\nЯ бот MCDonate и я призван помогать. Пока ничего не умею."));
		
		$command = explode(' ', $this->message['text']);
		$lc_command = trim(mb_convert_case($this->message['text'], MB_CASE_LOWER, 'utf-8'));
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => $lc_command));
	
		if ($lc_command == '/сколько'){
						
			$dirs = scandir('../unique_names/');
			
			$unique_names = array();
			foreach ($dirs as $_file){
				
				if ($_file == '.' OR $_file == '..')
					continue ;
				
				$content = unserialize(file_get_contents('../unique_names/' . $_file));
				
				$unique_names[] = $content['post']['member_name'];
			}


			$text = "Всего %s\r\n\r\n";

			$text = sprintf($text, count($unique_names));
			
			foreach ($unique_names as $_key => $_name)
				$text .= ($_key + 1) . '. ' . $_name . "\r\n";
				
			apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));
			
			$this->clearCallbackAction();
		}
		
	
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
		//$this->memcache = new Memcache;
		//$this->memcache->connect('127.0.0.1', 11211) or die ("Could not connect");			
	}
	
	private function askUserForAuth(){
		
		$authState = $this->memcache->get('auth_' . $this->from_id);
		
		//$this->sendMessage('authState:' . $authState);		
		
		if ($authState == false){
			$this->sendMessage("Здравствуйте!\r\nВы что-то хотите от бота (отметиться на смене, раздать листовки и т.д.), но у вас не привязан телегармм к сайту.\r\nЧтобы продолжить, введите свой номер телефона от сайта Gudkov.ru в формате +79...");
			$result = $this->memcache->set('auth_' . $this->from_id, 'hello', MEMCACHE_COMPRESSED, 120);
		}
		else {

			if ($this->message['text'] == ''){
				$this->sendMessage("Чтобы продолжить, введите свой номер телефона от сайта Gudkov.ru");
				return ;
			}
				
			if ($authState == 'hello'){
				$phone = parseAndformatPhone($this->message['text']);
				$this->memcache->replace('auth_' . $this->from_id, 'phone', MEMCACHE_COMPRESSED, 120);
				$this->memcache->set('phone_' . $this->from_id, $phone, MEMCACHE_COMPRESSED, 120);
				
				$this->sendMessage("Теперь введите свой пароль от сайта");
				
			}
			
			if ($authState == 'phone'){
				
				$phone = $this->memcache->get('phone_' . $this->from_id);

				$isAuthSuccess = $this->authUserByCredentials($phone, $this->message['text']);
			
				if (!$isAuthSuccess){
					$this->sendMessage("Неправильный телефон или пароль");
					$this->sendMessage("Чтобы продолжить, введите свой номер телефона от сайта Gudkov.ru");
					$this->memcache->replace('auth_' . $this->from_id, 'hello', MEMCACHE_COMPRESSED, 60);
					return ;
				}

			
				$this->user->telegram_id = $this->from_id ;
				$this->user->save();
				
				$this->sendMessage("Ура! Вы успешно привязали телеграмм к аккаунту на Gudkov.ru");
				$this->sendMessage("\r\nОбязательно удалите свой пароль из этого диалога!");
			}
			
		}
		
	}
	
	private function askUserForAuth_old(){
		
		$parts = explode(' ', $this->message['text']);
		
		if (count($parts) == 2){
			
			$isAuthSuccess = $this->authUserByCredentials($parts[0], $parts[1]);
		
			if (!$isAuthSuccess){
				$this->sendMessage("Неправильный телефон или пароль");
				return ;
			}
		
			$this->user->telegram_id = $this->from_id ;
			$this->user->save();
			
			$this->sendMessage("Ура! Вы успешно привязали телеграмм к аккаунту на Gudkov.ru");
			$this->sendMessage("Не забудьте обязательно удалить свой пароль из этого диалога!");
			
		}
		else {
			$this->sendMessage("Здравствуйте!\r\nЧтобы продолжить, введите свой номер телефона от сайта Gudkov.ru в формате +79...");
		}
	}
	
	
	static function getMarkup($markup_name, $args = array()){
		
		if ($markup_name == 'msg_req_markup'){
			return $msg_req_markup = json_encode(array(
					'inline_keyboard' => array(
						array (
							array(
								'text' => 'Прокомментировать заявку',
								'callback_data' => json_encode(array(
									'user_id' => (string) $args['target_req']->read_attribute('id'),
									'act' => 'msg_req',
								)),
							),
						),
					),
				));				
		}
		
		if ($markup_name == 'control_buttons'){
			return $msg_req_markup = json_encode(array(
					'inline_keyboard' => array(
						array (
							array(
								'text' => 'Блок. магазин',
								'callback_data' => json_encode(array(
									'req_args' => array('login'),
									'act' => 'block_shop',
								)),
							),
							array(
								'text' => 'Блок. выплаты',
								'callback_data' => json_encode(array(
									'req_args' => array('login'),
									'act' => 'block_payment',
								)),
							),
							array(
								'text' => 'Задать комиссию',
								'callback_data' => json_encode(array(
									'req_args' => array('login', 'new_tax', 'reason'),
									'act' => 'our_tax_setter',
								)),
							),
							array(
								'text' => 'Низкая комиссия',
								'callback_data' => json_encode(array(
									'req_args' => array(),
									'act' => 'show_low_taxes',
								)),
							),
						),
					),
				));				
		}
		
	}
	
	public function sendMessage($text, $target = ''){
		
		if ($this->chat_id == 0){
			$target = $this->from_id;
		}
		else {
			$target = $this->chat_id;
		}		

		
		apiRequest("sendMessage", array('chat_id' => $target, "text" => $text));
	}
	
	public function findInArrayByValue($key, $value, $target){
		
		foreach ($target as $_target){
			if (is_object($_target)){
				if ($_target->__isset($key) AND $_target->read_attribute($key) == $value)
					return $_target ;						
			}
			else {
				if (isset($_target[$key]) AND $_target[$key] == $value)
					return $_target ;				
			}
		}
		
		return false;
	}
	
	private function checkAuth(){
		
		$allowedTelegram = array('159147853', '344702', '1223639', '221693', '52376382');
		
		if (!in_array($this->from_id, $allowedTelegram))
			return false;
		
		
		//$this->user = db_user::find_by_login('xopbatgh');
		
		return true;
		
	}
	
	private function clearCallbackAction(){
		
		//$callback_active = $this->memcache->delete('callback_active_' . $this->from_id);
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'var_dump' . $callback_active));
		
		exit();
	}
	
	private function cqGetArg($arg_name, &$data){
			
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'req'));
		//return ;
		if ($arg_name == 'new_tax'){

			if (!isset($data['args_ok']['new_tax']) AND $data['args_waiting'] == 'new_tax'){
				$text = "Укажите новый размер комиссии для " . $this->message['data']['args_ok']['login'] . " (от 1 до 100)";
				
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));		
				
				$this->message['data']['args_waiting'] = 'new_tax';
				$data['args_ok']['new_tax'] = '';
			}
			else if ($this->message['data']['args_waiting'] == 'new_tax' AND $data['args_ok']['new_tax'] == ''){
				
				$new_tax = trim($this->message['text']);
				
				//$owner = $this->parseTarget($this->message['text']);
				
				if (!is_numeric($new_tax) OR $new_tax < 0 OR $new_tax > 100){
					apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Указан неправильный процент'));
				}
				else {
					$data['args_ok']['new_tax'] = $new_tax ;
					
					//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Setted'));		
					
					$this->message['data']['args_waiting'] = '';
				}
				
			}
			else {
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Some error target new_tax'));			
			}

		
		}
		
		if ($arg_name == 'reason'){

			if (!isset($data['args_ok']['reason']) AND $data['args_waiting'] == 'reason'){
				$text = "Укажите новый размер комиссии для " . $this->message['data']['args_ok']['login'] . " (от 1 до 100)";
				
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));		
				
				$this->message['data']['args_waiting'] = 'reason';
				$data['args_ok']['reason'] = '';
			}
			else if ($this->message['data']['args_waiting'] == 'reason' AND $data['args_ok']['reason'] == ''){
				
				$reason = trim($this->message['text']);
				
				//$owner = $this->parseTarget($this->message['text']);
			
				$data['args_ok']['reason'] = $reason ;
				
				//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Setted'));		
				
				$this->message['data']['args_waiting'] = '';
			
				
			}
			else {
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Some error target reason'));			
			}

		
		}
		
		if ($arg_name == 'login')
			$this->cqIsTargetLoginExists($data);
		
	}
	
	private function cqIsTargetLoginExists(&$data){
			
		if (!isset($data['args_ok']['login']) AND $data['args_waiting'] == 'login'){
			$text = "Укажите логин или ссылку на магазин";
			
			apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));		
			
			$this->message['data']['args_waiting'] = 'login';
			$data['args_ok']['login'] = '';
		}
		else if ($this->message['data']['args_waiting'] == 'login' AND $data['args_ok']['login'] == ''){
			
			
			$owner = $this->parseTarget($this->message['text']);
			
			if (empty($owner)){
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Не найдено ничего похожего на "' . $this->message['text'] . '" :('));
			}
			else {
				$data['args_ok']['login'] = $owner->read_attribute('login'); 
				$this->targetOwner = $owner ;
				
				//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Setted'));		
				
				$this->message['data']['args_waiting'] = '';
			}
			
		}
		else {
			apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Some error target login'));			
		}

		
	}
	
	private function parseTarget($target){

		$owner = '';
		// Это логин
		if (strpos($target, '.') === false){
			$owner = db_user::find_by_login($target);
		}
		// Это адрес магазина
		else {
			$target = str_replace('http://', '', $target);
			if ($target[strlen($target) - 1] == '/') $target = substr($target, 0, -1);
			
			$conditions = array('external_domain=?', $target);
			if (strpos($target, '.mcdonate.ru') !== false){
				$conditions = array('domain=?', str_replace('.mcdonate.ru', '', $target));
			}
			
			$shop = db_serversList::find('first', array(
				'conditions' => $conditions,
			));
			
			$owner = db_user::find_by_id($shop->read_attribute('owner_id'));
						
		}

		return $owner ;	
	}
	
	/*
		Ожидается, что здесь мы уже получили логин или пароль, а если нет, то сбрасываем до предыдущего состояния
	*/
	private function processCallbackQueryEngine(){
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'processCallbackQueryEngine'));
		

		
		$this->checkRequiredArgsForCallback();
		
		if (isset($this->message['data']['args_ok']['login']))
			$this->targetOwner = db_user::find_by_login($this->message['data']['args_ok']['login']) ;
		
		
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => json_encode($this->message['data'])));
		
		if ($this->message['data']['args_waiting'] == ''){
			$callbackFunction = 'callbackCq_' . $this->message['data']['act'] ;
			
			
			
			if (!method_exists($this, $callbackFunction)){
				apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'not found ' . $callbackFunction));
				return ;
			}
			
			$this->$callbackFunction();
		}
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => json_encode($this->message['data'])));
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => json_encode($this->message['text'])));
		
	}
	
	private function callbackCq_show_low_taxes(){
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'List of low tax'));
		
		$users = db_user::find('all', array(
			'select' => 'id, login, tax_amount, (SELECT last_bought_time FROM `serversList` WHERE serversList.owner_id=users.id ORDER BY serversList.last_bought_time DESC LIMIT 1) as last_bought_time, (SELECT SUM(amount+our_amount) FROM `payments_history` WHERE owner_id=users.id AND date>=NOW() - INTERVAL 30 DAY) as income',
			'conditions' => array('tax_amount!=0'),
			'order' => 'income ASC',
		));
		
		$output = 'Всего с пониженной комиссией %s человек.' . "\r\n";
		$output = sprintf($output, count($users));
		
		foreach ($users as $_key => $_user){
			
			$text = '%s %s (%s), оборот %s руб. посл. покупка %s';
			
			$text = sprintf($text, ($_key + 1) . '.', $_user->read_attribute('login'), $_user->read_attribute('tax_amount') . '%', floor($_user->read_attribute('income')) ,substr($_user->read_attribute('last_bought_time'), 0, 10));
			
			$output .= $text . "\r\n";
			
		}
			
		
		apiRequest("sendMessage", array('chat_id' => $this->from_id, "text" => $output));
		
		$this->clearCallbackAction();
		
	}
	
	private function callbackCq_our_tax_setter(){
		
		$previous_tax = $this->targetOwner->read_attribute('tax_amount');

		$this->targetOwner->tax_amount = $this->message['data']['args_ok']['new_tax'];
		
		$text = 'Новая комиссия %s (вместо %s) у %s сохранена';
		$text = sprintf($text, $this->message['data']['args_ok']['new_tax'], $previous_tax, $this->targetOwner->read_attribute('login'));
		
		$this->targetOwner->save();
		
		apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));
	
		createGdLogEntry('new_tax', array(
			'tg_bot_status' => 'none',
			'target_id' => $this->user->read_attribute('id'),
			'new_tax' =>  $this->message['data']['args_ok']['new_tax'],
			'reason' =>  $this->message['data']['args_ok']['reason'],
		));
		
		$this->clearCallbackAction();
		
	}
	
	private function callbackCq_block_payment(){
		
		
		
		if ($this->targetOwner->read_attribute('is_payment_blocked') == 1){
			$text = 'Выплаты разблокированы для логина %s (%s руб.)';
			$this->targetOwner->is_payment_blocked = 0;
		}
		else {
			$text = 'Выплаты заблокированы для логина %s (%s руб.)';
			$this->targetOwner->is_payment_blocked = 1;
		}
		
		$text = sprintf($text, $this->targetOwner->read_attribute('login'), $this->targetOwner->read_attribute('money'));
		
		$this->targetOwner->save();
		
		apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));
		
		
		$this->clearCallbackAction();
	}
	
	private function callbackCq_block_shop(){
		
		if ($this->targetOwner->read_attribute('is_banned') == 1){
			$text = 'Доступ разблокирован для логина %s (%s руб.)';
			$this->targetOwner->is_banned = 0;
		}
		else {
			$text = 'Доступ заблокирован для логина %s (%s руб.)';
			$this->targetOwner->is_banned = 1;
		}
		
		$text = sprintf($text, $this->targetOwner->read_attribute('login'), $this->targetOwner->read_attribute('money'));
		
		$this->targetOwner->save();
		
		apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => $text));
		
		$this->clearCallbackAction();
		
	}
		
	private function checkRequiredArgsForCallback(){
	
		if (isset($this->message['data']['args_waiting']) AND !empty($this->message['data']['args_waiting']))
			$this->cqGetArg($this->message['data']['args_waiting'], $this->message['data']);

		if (!isset($this->message['data']['args_waiting']))
			$this->message['data']['args_waiting'] = '';
		
		if (!isset($this->message['data']['args_ok']))
			$this->message['data']['args_ok'] = array();
		
		$possibleArgs = array('login', 'new_tax', 'comment');
		
		foreach ($possibleArgs as $_arg){
			if (empty($this->message['data']['req_args']))
				break ;
			
			if (in_array($_arg, $this->message['data']['req_args']) AND !isset($this->message['data']['args_ok'][$_arg])){
				$this->message['data']['args_waiting'] = $_arg;
				$this->cqGetArg($_arg, $this->message['data']);
				break ;
			}			
		}
		

		/*
		if (in_array('new_tax', $this->message['data']['req_args']) AND $this->message['data']['args_waiting'] == '' AND !isset($this->message['data']['args_ok']['new_tax'])){
			$this->message['data']['args_waiting'] = 'new_tax';
			$this->cqGetArg('new_tax', $this->message['data']);
		}
		*/
	
	}
	
	
	public function processCallbackQueryInit(){
		Global $chat_id ;	
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'processCallbackQueryInit'));
		
		$this->message['data'] = json_decode($this->message['data'], true);
		$action = $this->message['data']['act'];		
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => json_encode($this->message['data'])));
		
		if (empty($this->message['data']['req_args'])){
			//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'processCallbackQueryInit1'));
			
			$this->processCallbackQueryEngine();
			
		}
		else {
		// parse args
			$this->checkRequiredArgsForCallback();
		}

		return ;
		
		if (!isset($this->message['data']['act']) OR $this->message['data']['act'] != 'msg_req'){
			apiRequest("sendMessage", array('chat_id' => $this->from_id, "text" => 'Неудачное действие'));
			return ;		
		}
		
		if (!is_array($this->message['data'])){
			apiRequest("sendMessage", array('chat_id' => $this->from_id, "text" => 'Не удалось распознать ответ'));
			return ;
		}
		
		$user = db_main_users::find_by_id($this->message['data']['user_id']);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $this->from_id, "text" => 'Не найден получатель сообщения'));
			return ;
		}
		
		
		$text = 'Следующее ваше сообщение будет воспринято как комментарий к заявке %s (+%s)' . "\r\n" . "Пишите, у вас 1 минута!";
		
		$text = sprintf($text, $user->read_attribute('surname') . ' ' . $user->read_attribute('name'), $user->read_attribute('phone'));
		
		apiRequest("sendMessage", array('chat_id' => $this->from_id, "text" => $text));
		

		$result = $this->memcache->set('msg_req_' . $this->from_id, $user->read_attribute('id'), MEMCACHE_COMPRESSED, 60);
				  
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => serialize($result)));
		
		//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Получена инфа: ' . $this->message['data']));

		
		return ;
		
	}
	
	public function processOnbotMessages(){
		
		return ;	
		
	}
	
	
}

$gudkovBot = array();

function processTgQuery($message){


	
	
	$gudkovBot = new gudkovBot($message);
	
	if ($gudkovBot->is_ready == false)
		return ;
	
	//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['main_chat'], "text" => 'Получена инфа: '));
	
	
	//if ($)
	
	//$gudkovBot->sendMessage('Я вас уже узнаю, ' . json_encode($gudkovBot->act) . '!', 'user');
	
	//$gudkovBot->sendMessage('Я вас уже узнаю', 'user');


	processMessage($gudkovBot->message, true);
	
	exit();
	
	if ($gudkovBot->user->read_attribute('role') == 'admin'){

		  processMessage($gudkovBot->message, true);
		
	}
	else {

		if ($gudkovBot->act == 'callback_query'){
			$gudkovBot->processCallbackQuery();
			return ;
		}
		

		$gudkovBot->processOnbotMessages();

		
		
		
		
		//$gudkovBot->sendMessage('Я вас уже узнаю, ' . $gudkovBot->user->read_attribute('name') . '!');
	}	
}

function processCallbackQuery($message, $ignoreGudkovBot = false){
	Global $chat_id, $gudkovBot ;	

	apiRequest("sendMessage", array('chat_id' => $message['from']['id'], "text" => 'Модуль processCallbackQuery отключён'));
	return ;
		
	if ($ignoreGudkovBot == false){
		$gudkovBot = new gudkovBot($message);
		return ;
	}
	
	
	$message['data'] = json_decode($message['data'], true);
	
	if (!isset($message['data']['act']) OR $message['data']['act'] != 'msg_req'){
		apiRequest("sendMessage", array('chat_id' => $message['from']['id'], "text" => 'Неудачное действие'));
		return ;		
	}
	
	if (!is_array($message['data'])){
		apiRequest("sendMessage", array('chat_id' => $message['from']['id'], "text" => 'Не удалось распознать ответ'));
		return ;
	}
	
	$user = db_main_users::find_by_id($message['data']['user_id']);
	
	if (empty($user)){
		apiRequest("sendMessage", array('chat_id' => $message['from']['id'], "text" => 'Не найден получатель сообщения'));
		return ;
	}
	
	
	$text = 'Следующее ваше сообщение будет воспринято как комментарий к заявке %s (+%s)' . "\r\n" . "Пишите, у вас 1 минута!";
	
	$text = sprintf($text, $user->read_attribute('surname') . ' ' . $user->read_attribute('name'), $user->read_attribute('phone'));
	
	apiRequest("sendMessage", array('chat_id' => $message['from']['id'], "text" => $text));
	

	$memcache = new Memcache;
	$memcache->connect('127.0.0.1', 11211) or die ("Could not connect");
	
	$result = $memcache->set('msg_req_' . $message['from']['id'], $user->read_attribute('id'), MEMCACHE_COMPRESSED, 60);
			  
	//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => serialize($result)));
	
	//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Получена инфа: ' . $message['data']));

	
	return ;
	
}

function processMessage($message, $ignoreGudkovBot = false) {
	Global $chat_id, $gudkovBot ;
	file_put_contents('./tg_message', serialize($message));	

	/*
	if ($ignoreGudkovBot == false){
		$gudkovBot = new gudkovBot($message);
		
		return ;
	}
	*/

	
	// process incoming message
	$message_id = $message['message_id'];
	//$chat_id = $message['chat']['id'];

	//if (substr($message['text'], 0, strlen('@GudkovLive_bot')) != '@GudkovLive_bot') return ;
	
	$command = explode(' ', $message['text']);
	
	
	
	
	//print '<pre>' . print_r($message, true) . '</pre>';
  
}

/*
	Cron
*/


if (isset($_GET['cron'])){
	
	
}



if (isset($_GET['cron_bought'])){
	
	
	//print $query ;
	
	//print '<pre>' . print_r($boughts, true) . '</pre>';
	
}


define('WEBHOOK_URL', 'https://podpishi.org/static/form/tg/tg_appleFormLive.php');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$chat_id = $_POST['botChannels']['my'];
$chat_id = $_POST['botChannels']['main_chat'];



//apiRequest("sendMessage", array('chat_id' => $_POST['botChannels']['my'], "text" => 'Не найден act ' . $this->act));


//date>="2016-12-19 00:00:00"


if (isset($_GET['new_appleForm'])){
	
	$text = "Округ: %s\r\nОтделение: %s\r\nФИО: %s\r\n\r\n%s";
	
	$text_link = 'https://podpishi.org/static/form/pdf_generated/' . $_GET['generated_name'] . '.pdf';
	
	$text = sprintf($text, $_GET['district'], $_GET['spawn'], $_GET['member_name'], $text_link);
	
	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
	
	exit();
}			


if (isset($_GET['manual'])){
	print 'wtf';


			$rebillingOn = db_payments_list::find('first', array(
				'conditions' => array('is_payed=1 AND rebilling_aviso="1" AND payment_id NOT LIKE "rcard-%" AND date>="2016-12-19 00:00:00"'),
				'select' => 'count(*) as amount, sum(sum) as sum_total, max(sum) as sum_max'
			));	
	
	print '<pre>' . print_r($getUsedTreesArray, true) . '</pre>';
	
	exit();
	
}


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
