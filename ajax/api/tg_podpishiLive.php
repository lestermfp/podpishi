<?php
include('../../config.php');


	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
	

define('BOT_TOKEN', '214121158:AAF2Q3NHnDcsMwjtLlL0QcmdYFKdKy_iVqg');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', 'https://podpishi.org/ajax/api/tg_podpishiLive.php');

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

function processMessage($message) {
	Global $chat_id, $_CFG ;
	
	// process incoming message
	$message_id = $message['message_id'];
	$chat_id = $message['chat']['id'];

	$message['text'] = strtolower($message['text']);
	
	$command = explode(' ', $message['text']);
	$path_to_print_mode = $_CFG['root'] . 'config/print_mode.nfo';
	$path_to_current_session = $_CFG['root'] . 'config/current_session.nfo';
	
	if (strpos($message['text'], 'печать') !== false){
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}
		
		if ($user->read_attribute('is_approved') == 1){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Петиция уже подтверждена ' . $user_id));
			return ;			
		}
		
		$user->is_approved = 1 ;
		$user->save();
	
		$text = 'SUCCESS! Петиция №%s подтверждена';
		
		$text = sprintf($text, $user->read_attribute('id'));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
	}
	else if (strpos($message['text'], 'заново') !== false){
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}

		
		$user->is_approved = 1 ;
		$user->queue_date = date('Y-m-d H:i:s', time() - 3600);
		$user->is_sent = 0 ;
		$user->save();
	
		$text = 'SUCCESS! Петиция №%s (%s) вновь поставлена в очередь';
		
		$text = sprintf($text, $user->read_attribute('id'), $user->read_attribute('name'));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
	}
	else if (strpos($message['text'], 'склонить') !== false){
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}
		
		$name_ncl = strpos($message['text'], 'склонить');
		$name_ncl = trim(substr($message['text'], $name_ncl + strlen('склонить'), -1 * (strlen($user_id))));
		
		$user->name_ncl = $name_ncl ;
		$user->save();
	
		$text = 'SUCCESS! Новое склонение "%s" для "%s"';
		
		$text = sprintf($text, $name_ncl, $user->read_attribute('name'));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
	}
	else if (strpos($message['text'], 'сколько') !== false){
		
		$user_id = $command[count($command) - 1];

		$sent_total = db_main_users::count(array(
			'conditions' => 'is_sent=1',
		));
		
		$sent_meriya = db_main_users::count(array(
			'conditions' => 'is_sent=1 AND destination="meriya"',
		));
		
		$sent_mosgorduma = db_main_users::count(array(
			'conditions' => 'is_sent=1 AND destination="mosgorduma"',
		));
		
		$sent_gosduma = db_main_users::count(array(
			'conditions' => 'is_sent=1 AND destination="gosduma"',
		));
		$sent_meriya_velo = db_main_users::count(array(
			'conditions' => 'is_sent=1 AND destination="meriya_velo"',
		));
		$sent_mosgorduma_velo = db_main_users::count(array(
			'conditions' => 'is_sent=1 AND destination="mosgorduma_velo"',
		));
		
		$users_dispproved = db_main_users::count(array(
			'conditions' => 'is_approved=0',
		));
		
		$mosgorduma_wait = db_main_users::count(array(
			'conditions' => 'is_sent=0 AND is_approved=1 AND destination="mosgorduma"',
		));
		$meriya_wait = db_main_users::count(array(
			'conditions' => 'is_sent=0 AND is_approved=1 AND destination="meriya"',
		));
		$gosduma_wait = db_main_users::count(array(
			'conditions' => 'is_sent=0 AND is_approved=1 AND destination="gosduma"',
		));
		$meriya_velo_wait = db_main_users::count(array(
			'conditions' => 'is_sent=0 AND is_approved=1 AND destination="meriya_velo"',
		));
		$mosgorduma_velo_wait = db_main_users::count(array(
			'conditions' => 'is_sent=0 AND is_approved=1 AND destination="mosgorduma_velo"',
		));


		$text = 'Распечатано на данный момент:  ' . $sent_total .' шт.' . "\r\n";
		$text .= 'Распечатано meriya:  ' . $sent_meriya .' шт.' . "\r\n";
		$text .= 'Распечатано mosgorduma:  ' . $sent_mosgorduma .' шт.' . "\r\n";
		$text .= 'Распечатано gosduma:  ' . $sent_gosduma .' шт.' . "\r\n";
		$text .= 'Распечатано meriya_velo:  ' . $sent_meriya_velo .' шт.' . "\r\n";
		$text .= 'Распечатано mosgorduma_velo:  ' . $sent_mosgorduma_velo .' шт.' . "\r\n";
		$text .= 'Ожидают в очереди (meriya): ' . $meriya_wait . " шт. \r\n";
		$text .= 'Ожидают в очереди (mosgorduma): ' . $mosgorduma_wait . " шт. \r\n";
		$text .= 'Ожидают в очереди (gosduma): ' . $gosduma_wait . " шт. \r\n";
		$text .= 'Ожидают в очереди (meriya_velo): ' . $meriya_velo_wait . " шт. \r\n";
		$text .= 'Ожидают в очереди (mosgorduma_velo): ' . $mosgorduma_velo_wait . " шт. \r\n";
		$text .= 'Всего отклонено: ' . $users_dispproved . " шт. \r\n";
		
		//$text = sprintf($text, $users_amount);
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
	}
	else if (strpos($message['text'], 'телефон') !== false){
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}
		
		$phone = strpos($message['text'], 'телефон');
		$phone = trim(substr($message['text'], $phone + strlen('телефон'), -1 * (strlen($user_id))));
		
		$old_phone = $user->read_attribute('phone');
		
		$user->phone = $phone ;
		$user->save();
	
		$text = 'SUCCESS! Новый телефон "%s" для "%s"';
		
		$text = sprintf($text, $phone, $old_phone);
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
	}
	else if (strpos($message['text'], 'режим') !== false){
		
		$mode = $command[count($command) - 1];

		if ($mode == '/режим'){
			
			$text = 'Текущий режим печати: ' . file_get_contents($path_to_print_mode);
		}
		else {
			
			if (in_array($mode, array('meriya', 'mosgorduma', 'gosduma', 'meriya_velo', 'mosgorduma_velo'))){
				file_put_contents($path_to_print_mode, $mode);
				
				$text = 'Новый режим печати установлен: ' . file_get_contents($path_to_print_mode);
			}
			else {
				$text = 'Введённый режим печати не распознан';
			}
		
			
		}
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));

		
	}
	else if (strpos($message['text'], 'убрать') !== false){
		
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}
		
		if ($user->read_attribute('is_approved') == 0){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Петиция уже убрана ' . $user_id));
			return ;			
		}
		
		if ($user->read_attribute('is_sent') == 1){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Опоздали, петиция уже отправлена в печать' . $user_id));
			return ;			
		}
		
		$user->print_session_name = '' ;
		$user->is_approved = 0 ;
		$user->save();
	
		$text = 'SUCCESS! Петиция №%s убрана';
		
		$text = sprintf($text, $user->read_attribute('id'));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos($message['text'], 'статус') !== false){
		
		
		$user_id = $command[count($command) - 1];

		$user = db_main_users::find_by_id($user_id);
		
		if (empty($user)){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Не найдена петиция ' . $user_id));
			return ;
		}
		
		$status = 'сейчас в очереди.';
		if ($user->read_attribute('is_sent') == 1)
			$status = 'распечатана.';
		
		$text = 'SUCCESS! Петиция №%s ' . $status;
		
		$text = sprintf($text, $user->read_attribute('id'));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos(strtolower($message['text']), 'Сеанс печати создать') !== false){
		
		$print_session_name = $command[count($command) - 1];

		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=?', $print_session_name),
		));
		
		if ($amount > 0){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Сеанс печати уже существует с именем ' . $print_session_name));
			return ;
		}
		
		$query = "UPDATE `main_users` SET print_session_name='" . $print_session_name. "' WHERE print_session_name='' AND is_sent=0 AND is_approved=1 AND destination='" . file_get_contents($path_to_print_mode) . "'";
		db_main_users::connection()->query($query);
		
		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=?', $print_session_name),
		));
		
		$text = 'SUCCESS! Создан новый сеанс печати "%s", который охватил %s обращений.' . "\r\n" . 'Проверьте склонения и отправляйте на печать  https://podpishi.org/ajax/ajax_flow.php?context=petitionReport&print_session_name=%s';
		
		$text = sprintf($text, file_get_contents($path_to_print_mode), $amount, urlencode($print_session_name));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos(strtolower($message['text']), 'Сеанс печати информация') !== false){
		
		$print_session_name = $command[count($command) - 1];

		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=?', $print_session_name),
		));
		
		$amount_printed = db_main_users::count(array(
			'conditions' => array('print_session_name=? AND is_sent=1', $print_session_name),
		));
		
		$text = 'SUCCESS! Сеанс печати "%s", охватывает %s обращений, напечатано %s';
		
		$text = sprintf($text, $print_session_name, $amount, $amount_printed);
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos(strtolower($message['text']), 'Сеанс печати начать') !== false){
		
		$print_session_name = $command[count($command) - 1];

		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=?', $print_session_name),
		));
		
		if ($amount < 1){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Сеанс пуст - ' . $print_session_name));
			return ;				
		}
		
		file_put_contents($path_to_current_session, $print_session_name);
		
		$text = 'SUCCESS! Сеанс "%s" отправлен в печать';
		
		$text = sprintf($text, $print_session_name);
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos(strtolower($message['text']), 'Сеанс печати отчёт') !== false){
		
		$print_session_name = $command[count($command) - 1];

		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=?', $print_session_name),
		));
		
		if ($amount < 1){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Сеанс пуст - ' . $print_session_name));
			return ;			
		}
		
		$amount_left = db_main_users::count(array(
			'conditions' => array('print_session_name=? AND is_sent=0', $print_session_name),
		));
		
		if ($amount_left > 0){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Отчёт невозможен, не все обращения напечатаны (осталось ' . $amount_left . ') - ' . $print_session_name));
			return ;			
		}		
		
		$text = 'SUCCESS! Сеанс печати "%s", отчёт по ссылке - https://podpishi.org/ajax/ajax_flow.php?context=petitionReport&print_session_name=%s';
		
		$text = sprintf($text, $print_session_name, urlencode($print_session_name));
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else if (strpos(strtolower($message['text']), 'Сеанс печати удалить') !== false){
		
		$print_session_name = $command[count($command) - 1];

		$amount = db_main_users::count(array(
			'conditions' => array('print_session_name=? AND is_sent=1', $print_session_name),
		));
		
		if ($amount > 0){
			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Сеанс удалить невозможно, так как часть обращений уже напечатана ' . $print_session_name));
			return ;
		}
		
		$query = "UPDATE `main_users` SET print_session_name='' WHERE print_session_name='" . $print_session_name. "'";
		db_main_users::connection()->query($query);
		
		$text = 'SUCCESS! Удалён сеанс печати "%s"';
		
		$text = sprintf($text, $print_session_name);
		
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));
		
		
		
	}
	else {
		apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Извините, команда не распознана'));
	}
	
	
	
	//print '<pre>' . print_r($message, true) . '</pre>';
  
}

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}

$chat_id = '-130938339';

if (isset($_GET['send_messages1'])){

	$entries = db_main_activityLog::find('all', array(
		'conditions' => 'tg_bot_status="inqueue"',
		'order' => 'date ASC',
	));


	foreach ($entries as $_entry){

		$user = db_main_users::find_by_id($_entry->read_attribute('user_id'));

		if ($_entry->read_attribute('type') == 'petition_add'){

			$hours = $user->read_attribute('reg_date')->format('H');

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



			apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $text));

			$_entry->tg_bot_status = 'sent';
			$_entry->save();

		}

		//print '<pre>' . print_r($_entry, true) . '</pre>';
		//continue ;


	}

}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}



