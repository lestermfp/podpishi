<?php
include('../config.php');

require_once($_CFG['root'] . 'ajax/api/ncl/NCL.NameCase.ru.php');
	
/*
	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
*/
//print '<pre>' . print_r($_SESSION, true) . '</pre>';

class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.
	private $actionsPerDay = 10;
	private $allowed_dest = array('meriya', 'mosgorduma', 'meriya_velo', 'mosgorduma_velo'); //gosduma

    public function __construct() {

		$this->output['error'] = 'false';

        if (isset($_GET['context'])) {
            switch ($_GET['context']) {
				case 'savePetition':
                    $this->savePetition();
                    break;
				case 'savePetition_v2':
                    $this->savePetition_v2();
                    break;

            }
        }

		echo json_encode( $this->output );

    }


	private function savePetition_v2(){
		Global $_CFG ;

		//$_POST = unserialize(file_get_contents('./post'));
		//file_put_contents('./post', serialize($_POST));


		$this->checkIsActionNotLimitedByIp();
		if ($this->output['error'] != 'false') return ;

		//$_POST['destination'] = 'meriya';

		if (!isset($_POST['petition_text']) OR empty($_POST['petition_text']))
			$_POST['petition_text'] = '  ';

		$required_fields = array('name', 'city', 'email', 'house_number', 'street_name', 'appartment', 'destination', 'sign', 'petition_text', 'phone');

		$this->checkRequiredFields($_POST, $required_fields);
		if ($this->output['error'] != 'false') return ;

		$_POST['appartment_raw'] = $_POST['appartment'];
		$_POST['appartment'] = preg_replace('~\D+~', '', $_POST['appartment']);

		if ($_POST['appartment'] == ''){
			$this->output['error_text'] = 'Неправильно указан номер квартиры' ;
			$this->output['error'] = 'wrong appartment';
			return;
		}

		if (mb_strlen($_POST['petition_text']) > 4500){
			$this->output['error_text'] = 'Укоротите, пожалуйста, петицию, чтобы она поместилась на одну страницу.' ;
			$this->output['error'] = 'wrong petition_text length';
			return;
		}

		if (empty($_POST['destination'])){
			$this->output['error_text'] = 'Не выбран адресат для петиции' ;
			$this->output['error'] = 'wrong destination';
			return;
		}

		if (!in_array($_POST['destination'], $this->allowed_dest)){
			$this->output['error_text'] = 'Неправильный адресат' ;
			$this->output['error'] = 'wrong destination';
			return;
		}

		if (strpos($_POST['email'], '@') === false){
			$this->output['error_text'] = 'Указан непраильный E-mail' ;
			$this->output['error'] = 'wrong input email';
			$this->output['error_hightlight'] = 'email';
			return ;
		}

		$_POST['email'] = str_replace(' ', '', strtolower(trim($_POST['email'])));

		$this->checkUserSign();
		if ($this->output['error'] != 'false') return ;

		$this->checkUserAddress();
		if ($this->output['error'] != 'false') return ;

		//print '<pre>' . print_r($_POST, true) . '</pre>';
		//exit();

		$_POST['petition_text'] = htmlspecialchars($_POST['petition_text']);
		$is_manual_text = 1 ;
		$is_approved = 0 ;

		if (empty($_POST['petition_text']) == true OR strlen($_POST['petition_text']) < 20){
			$_POST['petition_text'] = $_CFG['petition_text'][$_POST['destination']] ;
			$is_manual_text = 0 ;
			$is_approved = 1;
		}




		$_POST['name'] = str_replace('ё', 'е', $_POST['name']);
		$unique_hash = md5($_POST['name'] . $_POST['city'] . $_POST['house_number'] . $_POST['street_name'] . $_POST['appartment'] . $_POST['destination']) ;

		$this->output['destinations_left'] = $this->calculateDestinationsLeft();


		$isExists = db_old_appeals::find_by_unique_hash($unique_hash);

		if (!empty($isExists)){
			$this->output['error'] = 'already inner 1 exists';
			$this->output['error_text'] = 'Вы уже отправляли петицию';
			$this->output['destination'] = 'none';
			return ;
		}


		// Если это госдума, то нам должны были прислать имя депутата
		if ($_POST['destination'] == 'gosduma'){

			if (!isset($_POST['gosduma_deputy']['name']))
				$_POST['gosduma_deputy']['name'] = 'empty';


			$gosduma_deputy = db_gosduma_deputy::find('first', array(
				'conditions' => array('name=?', $_POST['gosduma_deputy']['name']),
			));

			if (empty($gosduma_deputy)){
				$this->output['error'] = 'ntf mosgorduma deputy';
				$this->output['error_text'] = 'Депутат не выбран!';
				return ;
			}

		}

		//$_POST['name_ncl'] = $this->getNCLName($_POST['name']);
		$_POST['name_ncl'] = $_POST['name'];

		$newUser = array(
			'name' => $_POST['name'],
			'name_ncl' => $_POST['name_ncl'],
			'phone' => $_POST['phone'],
			'email' => $_POST['email'],
			'city' => $_POST['yd_address']['LocalityName'],
			'house_number' => $_POST['yd_address']['PremiseNumber'],
			'street_name' => $_POST['yd_address']['ThoroughfareName'],
			'lat' => $_POST['yd_address']['lat'],
			'lng' => $_POST['yd_address']['lng'],
			'appartment' => $_POST['appartment'],
			'appartment_raw' => $_POST['appartment_raw'],
			'sign' => $_POST['sign'],
			'unique_hash' => $unique_hash,
			'petition_text' => $_POST['petition_text'],
			'destination' => $_POST['destination'],
			'reg_date' => 'now',
			'queue_date' => 'now',
			'last_ip' => GetRealIp(),
			'is_manual_text' => $is_manual_text,
			'is_approved' => $is_approved,
			'is_on_trolley' => 'no',
		);

		//print '<pre>' . print_r($newUser, true) . '</pre>';
		//exit();

        /*
		$deputy = false ;
		if (isset($_POST['yd_reverse'])){
			$newUser['region_name'] = $_POST['yd_reverse']['region_name'];
			$newUser['district_name'] = $_POST['yd_reverse']['district_name'];

			$deputy = $this->detectDeputyByAddress();

			if ($deputy == false){
				$deputy = array(
					'name' => 'Шапошников Алексей Валерьевич',
					'sex' => 'm',
					'name_ncl' => $this->getNCLName('Шапошников Алексей Валерьевич', 'all'),
				);
			}

			$this->output['deputy']['mosgorduma'] = $deputy ;

			//$deputy = $this->detectGosdumaByAddress();
		}
        */

		//print '<pre>' . print_r($this->output, true) . '</pre>';
		//exit();

        /*
		if ($_POST['destination'] == 'gosduma'){
			$deputy_gosduma = array(
				'name' => $gosduma_deputy->read_attribute('name'),
				'sex' => $gosduma_deputy->read_attribute('sex'),
				'name_ncl' => $this->getNCLName($gosduma_deputy->read_attribute('name'), 'all'),
			);

			$this->output['deputy']['gosduma'] = $deputy_gosduma ;

			$deputy = $deputy_gosduma ;
		}


		if ($deputy !== false){
			$newUser['deputy_name'] = $deputy['name'];
			$newUser['deputy_sex'] = $deputy['sex'];
		}
        */


		$this->output['destination'] = $_POST['destination'];

		$tmp = $this->output['destinations_left'] ;
		$this->output['destinations_left'] = array();

		foreach ($tmp as $_value)
			$this->output['destinations_left'][] = $_value ;

		//return ;
		//print '<pre>' . print_r($this->output, true) . '</pre>';
		//print '<pre>' . print_r($newUser, true) . '</pre>';
		//exit();


		try {
			$this->user = db_old_appeals::create($newUser);
		}
		catch (Exception $e) {

			// If default exception
			if ($e->getCode() == 23000){
				$this->output['error'] = 'already inner 1 exists';
				$this->output['error_text'] = 'Не удалось сохранить петицию, попробуйте снова';
			}
			// If not default, log it
			else {
				$this->output['error'] = 'already inner 1 exists';
				$this->output['error_text'] = 'Не удалось сохранить петицию, попробуйте снова (2)';
			}

			return ;
		}


		if ($this->output['error'] == 'false'){

			$template_name = false ;

			foreach ($_CFG['template_params'] as $_template_name => $_params){

				foreach ($_params['petitions'] as $_petition){

					if ($_petition['id'] == $_POST['destination']){
						$template_name = $_template_name ;

						break 2;
					}
				}

			}

			if (in_array($_POST['destination'], array('meriya', 'meriya_velo'))){
				$this->addToTrolleyMap($template_name);
				$_SESSION['prefilled'] = array(
					'name' => $_POST['name'],
					'phone' => $_POST['phone'],
					'email' => $_POST['email'],
					'city' => $_POST['yd_address']['LocalityName'],
					'house_number' => $_POST['yd_address']['PremiseNumber'],
					'street_name' => $_POST['yd_address']['ThoroughfareName'],
					'appartment_raw' => $_POST['appartment_raw'],
				);
			}

			if ($_POST['destination'] == 'gosduma'){
				$query = "UPDATE `gosduma_deputy` SET amount=amount+1 WHERE name='" . $this->output['deputy']['gosduma']['name'] . "'";
                db_old_appeals::connection()->query($query);
			}


			$this->addToGoogleDocs();

			$newLog = array(
				'type' => 'petition_add',
				'user_id' => $this->user->read_attribute('id'),
				'ip' => GetRealIp(),
				'details' => base64_encode(serialize(
					array(
						'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					)
				)),
				'tg_bot_status' => 'inqueue',
			);

			db_main_activityLog::create($newLog);


		}


		//print '<pre>' . print_r($_POST, true) . '</pre>';

		//print '<pre>' . print_r($_POST, true) . '</pre>';
		//print '<pre>' . print_r($this->output, true) . '</pre>';

		//return ;
	}

	private function calculateDestinationsLeft(){

		$destinations_left = $this->allowed_dest ;

		foreach ($destinations_left as $_key => $_destination)
			if ($_destination == $_POST['destination'])
				unset($destinations_left[$_key]);

		// Находим какие петиции уже были отправлены
		$petitions = db_old_appeals::find('all', array(
			'conditions' => array('phone=?', $_POST['phone']),
			'select' => 'destination, phone, id',
		));

		foreach ($petitions as $_petition)
			if (in_array($_petition->read_attribute('destination'),$destinations_left)){

				foreach ($destinations_left as $_key => $_destination)
					if ($_destination == $_petition->read_attribute('destination'))
						unset($destinations_left[$_key]);
			}


		return $destinations_left;

	}


	private function checkRequiredFields($target, $required_fields){
		foreach ($required_fields as $_key){
			if (!isset($target[$_key]) OR empty($target[$_key])){
				$this->output['error_text'] = 'Недостаточно данных' ;
				$this->output['error'] = 'wrong input '  . $_key;
				$this->output['error_hightlight'] = $_key;
				return ;
			}
		}
	}

	private function reverseGeocode($coords, $kind = 'district'){

		$db_entry = db_yd_logs::find_by_address($coords);

		if (empty($db_entry)){

            $apikey = '9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa';

            $url = 'https://geocode-maps.yandex.ru/1.x/?format=json&apikey=' . $apikey . '&geocode=' . $coords . '&results=1&kind=' . $kind;

			$yd_reply = file_get_contents($url);

			print $yd_reply;

			$yd_reply = json_decode($yd_reply, true);

			var_dump($yd_reply);
			exit();

			if (!is_array($yd_reply)){
				$this->output['error_text'] = 'Не удалось получить информацию по ' . $coords ;
				$this->output['error'] = 'yes';
				return false;
			}

			$newYdLog = array(
				'address' => $coords,
				'yd_reply' => serialize($yd_reply),
			);

			db_yd_logs::create($newYdLog);

		}
		else {

			$yd_reply = unserialize($db_entry->read_attribute('yd_reply'));

		}
		return $yd_reply ;

	}

	private function getCoordsByAddress($address){

		$db_entry = db_yd_logs::find_by_address($address);

		if (empty($db_entry)){

            $apikey = '9e89ef7e-7675-4324-ba7b-5f2c2fcc91fa';

			$yd_reply = file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&apikey=' . $apikey . '&geocode=' . urlencode($address));

			$yd_reply = json_decode($yd_reply, true);

			//var_dump($yd_reply);

            //print '<pre>' . print_r($yd_reply, true) . '</pre>';

            //exit();
            
			if (!is_array($yd_reply)){
				$this->output['error_text'] = 'Не удалось получить информацию по адресу ' . $address ;
				$this->output['error'] = 'yes';
				$this->output['$yd_reply'] = $yd_reply;
				return false;
			}

			if (!isset($yd_reply['response']['GeoObjectCollection']['featureMember'][0])){
				$this->output['error_text'] = 'Не найдена информация по адресу ' . $address ;
				$this->output['error'] = 'yes';
                $this->output['$yd_reply'] = $yd_reply;
				return false;
			}

			$newYdLog = array(
				'address' => $address,
				'yd_reply' => serialize($yd_reply),
			);

			db_yd_logs::create($newYdLog);
		}
		else {

			$yd_reply = unserialize($db_entry->read_attribute('yd_reply'));

		}

		return $yd_reply ;
	}

	private function savePetition(){
		Global $_CFG ;

		//$_POST = unserialize(file_get_contents('post'));
		//file_put_contents('post', serialize($_POST));

		$this->checkIsActionNotLimitedByIp();
		if ($this->output['error'] != 'false') return ;

		//$_POST['destination'] = 'meriya';

		if (!isset($_POST['petition_text']) OR empty($_POST['petition_text']))
			$_POST['petition_text'] = '  ';

		$required_fields = array('name', 'city', 'email', 'house_number', 'street_name', 'appartment', 'destination', 'sign', 'petition_text', 'phone');

		$this->checkRequiredFields($_POST, $required_fields);
		if ($this->output['error'] != 'false') return ;

		$_POST['appartment_raw'] = $_POST['appartment'];
		$_POST['appartment'] = preg_replace('~\D+~', '', $_POST['appartment']);

		if ($_POST['appartment'] == ''){
			$this->output['error_text'] = 'Неправильно указан номер квартиры' ;
			$this->output['error'] = 'wrong appartment';
			return;
		}

		if (mb_strlen($_POST['petition_text']) > 3000){
			$this->output['error_text'] = 'Укоротите, пожалуйста, петицию, чтобы она поместилась на одну страницу.' ;
			$this->output['error'] = 'wrong petition_text length';
			return;
		}

		if (empty($_POST['destination'])){
			$this->output['error_text'] = 'Не выбран адресат для петиции' ;
			$this->output['error'] = 'wrong destination';
			return;
		}

		if (!in_array($_POST['destination'], $this->allowed_dest)){
			$this->output['error_text'] = 'Неправильный адресат' ;
			$this->output['error'] = 'wrong destination';
			return;
		}

		if (strpos($_POST['email'], '@') === false){
			$this->output['error_text'] = 'Указан непраильный E-mail' ;
			$this->output['error'] = 'wrong input email';
			$this->output['error_hightlight'] = 'email';
			return ;
		}

		$_POST['email'] = str_replace(' ', '', strtolower(trim($_POST['email'])));

		$this->checkUserSign();
		if ($this->output['error'] != 'false') return ;

		$this->checkUserAddress();
		if ($this->output['error'] != 'false') return ;


		$_POST['petition_text'] = htmlspecialchars($_POST['petition_text']);
		$is_manual_text = 1 ;
		$is_approved = 0 ;

		if (empty($_POST['petition_text']) == true OR strlen($_POST['petition_text']) < 20){
			$_POST['petition_text'] = $_CFG['petition_text'][$_POST['destination']] ;
			$is_manual_text = 0 ;
			$is_approved = 1;
		}

		$_POST['name'] = str_replace('ё', 'е', $_POST['name']);
		$unique_hash = md5($_POST['name'] . $_POST['city'] . $_POST['house_number'] . $_POST['street_name'] . $_POST['appartment'] . $_POST['destination']) ;

		$this->output['destinations_left'] = $this->allowed_dest ;
		foreach ($this->output['destinations_left'] as $_key => $_destination)
			if ($_destination == $_POST['destination'])
				unset($this->output['destinations_left'][$_key]);

		// Находим какие петиции уже были отправлены
		$petitions = db_old_appeals::find('all', array(
			'conditions' => array('phone=?', $_POST['phone']),
			'select' => 'destination, phone, id',
		));

		foreach ($petitions as $_petition)
			if (in_array($_petition->read_attribute('destination'),$this->output['destinations_left'])){

				foreach ($this->output['destinations_left'] as $_key => $_destination)
					if ($_destination == $_petition->read_attribute('destination'))
						unset($this->output['destinations_left'][$_key]);
			}


		$isExists = db_old_appeals::find_by_unique_hash($unique_hash);

		if (!empty($isExists)){
			$this->output['error'] = 'already inner 1 exists';
			$this->output['error_text'] = 'Сохранено, спасибо!';
			$this->output['destination'] = 'none';
			return ;
		}


		// Если это госдума, то нам должны были прислать имя депутата
		if ($_POST['destination'] == 'gosduma'){

			if (!isset($_POST['gosduma_deputy']['name']))
				$_POST['gosduma_deputy']['name'] = 'empty';


			$gosduma_deputy = db_gosduma_deputy::find('first', array(
				'conditions' => array('name=?', $_POST['gosduma_deputy']['name']),
			));

			if (empty($gosduma_deputy)){
				$this->output['error'] = 'ntf mosgorduma deputy';
				$this->output['error_text'] = 'Депутат не выбран!';
				return ;
			}

		}

		//$_POST['name_ncl'] = $this->getNCLName($_POST['name']);
		$_POST['name_ncl'] = $_POST['name'];

		$newUser = array(
			'name' => $_POST['name'],
			'name_ncl' => $_POST['name_ncl'],
			'phone' => $_POST['phone'],
			'email' => $_POST['email'],
			'city' => $_POST['yd_address']['LocalityName'],
			'house_number' => $_POST['yd_address']['PremiseNumber'],
			'street_name' => $_POST['yd_address']['ThoroughfareName'],
			'lat' => $_POST['yd_address']['lat'],
			'lng' => $_POST['yd_address']['lng'],
			'appartment' => $_POST['appartment'],
			'appartment_raw' => $_POST['appartment_raw'],
			'sign' => $_POST['sign'],
			'unique_hash' => $unique_hash,
			'petition_text' => $_POST['petition_text'],
			'destination' => $_POST['destination'],
			'reg_date' => 'now',
			'queue_date' => 'now',
			'last_ip' => GetRealIp(),
			'is_manual_text' => $is_manual_text,
			'is_approved' => $is_approved,
			'is_on_trolley' => 'no',
		);

		$deputy = false ;
		if (isset($_POST['yd_reverse'])){
			$newUser['region_name'] = $_POST['yd_reverse']['region_name'];
			$newUser['district_name'] = $_POST['yd_reverse']['district_name'];

			$deputy = $this->detectDeputyByAddress();

			if ($deputy == false){
				$deputy = array(
					'name' => 'Шапошников Алексей Валерьевич',
					'sex' => 'm',
					'name_ncl' => $this->getNCLName('Шапошников Алексей Валерьевич', 'all'),
				);
			}

			$this->output['deputy']['mosgorduma'] = $deputy ;

			//$deputy = $this->detectGosdumaByAddress();
		}


		if ($_POST['destination'] == 'gosduma'){
			$deputy_gosduma = array(
				'name' => $gosduma_deputy->read_attribute('name'),
				'sex' => $gosduma_deputy->read_attribute('sex'),
				'name_ncl' => $this->getNCLName($gosduma_deputy->read_attribute('name'), 'all'),
			);

			$this->output['deputy']['gosduma'] = $deputy_gosduma ;

			$deputy = $deputy_gosduma ;
		}

		if ($deputy !== false){
			$newUser['deputy_name'] = $deputy['name'];
			$newUser['deputy_sex'] = $deputy['sex'];
		}


		$this->output['destination'] = $_POST['destination'];

		$tmp = $this->output['destinations_left'] ;
		$this->output['destinations_left'] = array();

		foreach ($tmp as $_value)
			$this->output['destinations_left'][] = $_value ;

		//return ;
		//print '<pre>' . print_r($this->output, true) . '</pre>';
		//print '<pre>' . print_r($newUser, true) . '</pre>';
		//exit();


		try {
			$this->user = db_old_appeals::create($newUser);
		}
		catch (Exception $e) {

			// If default exception
			if ($e->getCode() == 23000){
				$this->output['error'] = 'already inner 1 exists';
				$this->output['error_text'] = 'Сохранено, спасибо!';
			}
			// If not default, log it
			else {
				$this->output['error'] = 'already inner 1 exists';
				$this->output['error_text'] = 'Сохранено, спасибо!';
			}

			return ;
		}


		if ($this->output['error'] == 'false'){


			if ($_POST['destination'] == 'meriya')
				$this->addToTrolleyMap();

			if ($_POST['destination'] == 'gosduma'){
				$query = "UPDATE `gosduma_deputy` SET amount=amount+1 WHERE name='" . $this->output['deputy']['gosduma']['name'] . "'";
                db_old_appeals::connection()->query($query);
			}


			$this->addToGoogleDocs();

			$newLog = array(
				'type' => 'petition_add',
				'user_id' => $this->user->read_attribute('id'),
				'ip' => GetRealIp(),
				'details' => base64_encode(serialize(
					array(
						'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					)
				)),
				'tg_bot_status' => 'inqueue',
			);

			db_main_activityLog::create($newLog);


		}


		//print '<pre>' . print_r($_POST, true) . '</pre>';

	}

	private function addToGoogleDocs(){


		$keys = array('id', 'destination', 'name', 'address', 'email', 'phone', 'deputy_name', 'reg_date');

		$address = $this->user->read_attribute('city') . ' ' . $this->user->read_attribute('street_name') . ' ' . $this->user->read_attribute('house_number') . ' кв. ' . $this->user->read_attribute('appartment') ;

		$args = '';
		$args .= '&id=' . $this->user->read_attribute('id');
		$args .= '&destination=' . $this->user->read_attribute('destination');
		$args .= '&name=' . $this->user->read_attribute('name');
		$args .= '&address=' . $address;
		$args .= '&email=' . $this->user->read_attribute('email');
		$args .= '&phone=' . $this->user->read_attribute('phone');
		$args .= '&deputy_name=' . urlencode($this->user->read_attribute('deputy_name'));
		$args .= '&thedate=' . urlencode($this->user->read_attribute('reg_date')->format('d.m.Y H:i'));

		$baseUrl = 'http://trolley.city4people.ru/map/ajax/ajax_docs.php?context=updateSpreadsheetPodpishi' . $args;

		//if (strpos($this->user->read_attribute('name'), 'Тест') !== false)
			//print $baseUrl ;

		file_get_contents($baseUrl);

	}

	private function getNCLName($name, $isEverything = false){
		# Объявляем объект класса.
		$case = new NCLNameCaseRu();

		# Метод q - склоняет Фамилию, Имя и Отчество человека по правилам пола.
		$array_ncl = $case->q($name);

		if ($isEverything)
			return $array_ncl ;

		return $array_ncl[1] ;
	}

	private function detectGosdumaByAddress(){

		$file = file('./api/deputy_gosduma.csv');
		$district_name = trim(str_replace('район', '', $_POST['yd_reverse']['district_name'])) ;
		$district_name = trim(str_replace('ё', 'е', $district_name)) ;

		foreach ($file as $_line){

			if (strpos($_line, $district_name) !== false){

				list($parsed['tmp'], $parsed['districts'], $parsed['name'], $parsed['sex']) = explode(',', $_line);

				$parsed['name_ncl'] = $this->getNCLName($parsed['name'], 'all');

				return $parsed ;

			}

		}

		return false ;
	}

	private function detectDeputyByAddress(){


		$file = file('./api/deputy_mgd.csv');
		$district_name = trim(str_replace('район', '', $_POST['yd_reverse']['district_name'])) ;
		$district_name = trim(str_replace('ё', 'е', $district_name)) ;

		foreach ($file as $_line){

			if (strpos($_line, $district_name) !== false){

				list($parsed['tmp'], $parsed['districts'], $parsed['name'], $parsed['sex']) = explode(',', $_line);

				$parsed['name_ncl'] = $this->getNCLName($parsed['name'], 'all');

				return $parsed ;

			}

		}

		return false ;
	}

	private function checkUserAddress(){

		$address = $_POST['city'] . ',' . $_POST['street_name'] . ' ' . $_POST['house_number'];

		$yd_reply = $this->getCoordsByAddress($address);
		if ($this->output['error'] != 'false') return ;


		if (!isset($yd_reply['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData'])){
			$this->output['error_text'] = 'Не найдена информация по адресу ' . $address ;
			$this->output['error'] = 'yes2';
			return false;
		}

		if (!isset($yd_reply['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData'])){
			$this->output['error_text'] = 'Не найдена информация по адресу ' . $address ;
			$this->output['error'] = 'yes2';
			return false;
		}

		$GeocoderMetaData = $yd_reply['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData'];

		$CountryNameCode = $this->recursiveFind($GeocoderMetaData, 'CountryNameCode');

		if (empty($CountryNameCode) OR $CountryNameCode != "RU"){
			$this->output['error_text'] = 'Введённый адрес не найден на территории РФ';
			$this->output['error'] = 'notInRussia';
			return false;
		}

		$PremiseNumber = $this->recursiveFind($GeocoderMetaData, 'PremiseNumber');
		$ThoroughfareName = $this->recursiveFind($GeocoderMetaData, 'ThoroughfareName');
		$DependentLocalityName = $this->recursiveFind($GeocoderMetaData, 'DependentLocalityName');
		$LocalityName = $this->recursiveFind($GeocoderMetaData, 'LocalityName');

		if (empty($ThoroughfareName) AND !empty($DependentLocalityName))
			$ThoroughfareName = $DependentLocalityName;

		if (empty($PremiseNumber)){
			$this->output['error_text'] = 'Не найдена информация по адресу ' . $address ;
			$this->output['error'] = 'yes3';
			return false;
		}

		$point = $yd_reply['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point'] ;
		list($lng, $lat) = explode(' ', $point['pos']);

		$_POST['yd_address'] = array(
			'ThoroughfareName' => $ThoroughfareName,
			'PremiseNumber' => $PremiseNumber,
			'LocalityName' => $LocalityName,
			'lng' => $lng,
			'lat' => $lat,
		);



		if ($LocalityName == 'Москва' AND 1 == 2){
			// get district
			$yd_reply_reverse = $this->reverseGeocode($point['pos']);
			if ($this->output['error'] != 'false') return ;

			$DependentLocality = $this->recursiveFind($yd_reply_reverse, 'DependentLocality');
			if (!empty($DependentLocality)){

				if (!isset($DependentLocality['DependentLocality']))
					$DependentLocality['DependentLocality']['DependentLocalityName'] = 'Не найден';


				$_POST['yd_reverse'] = array(
					'region_name' => $DependentLocality['DependentLocalityName'],
					'district_name' => str_replace('ё', 'е', $DependentLocality['DependentLocality']['DependentLocalityName']),
				);
			}

			if ($_POST['appartment'] == '373'){
				//print '<pre>' . print_r($yd_reply_reverse, true) . '</pre>';
			}
		}

		//print '<pre>' . print_r($_POST, true) . '</pre>';
		//exit();

	}

	private function addToTrolleyMap($map_type = false){

		$baseUrl = 'https://trolley.city4people.ru/map/ajax/ajax_trolley.php?context=addNewUserFromPodpishi';

		$keys = array('name', 'email', 'phone', 'city', 'street_name', 'house_number', 'appartment', 'lat', 'lng', 'destination');

		foreach ($keys as $_key){

			$baseUrl .= '&' . $_key . '=' . trim($this->user->read_attribute($_key)) . '';

		}

		if ($map_type != false)
			$baseUrl .= '&map_type=' . $map_type . '';

		//print $baseUrl . '<br/>';

		//file_put_contents('./addToTrolleyMap', $baseUrl . "\r\n", FILE_APPEND);

		$reply = @json_decode(@file_get_contents($baseUrl), true);

		$this->output['reply'] = $reply ;

		if (is_array($reply) AND $reply['error'] == 'false'){
			$this->user->is_on_trolley = 'yes';
			$this->user->save();
		}
	}

	public function recursiveFind(array $array, $needle)
	{
		$iterator  = new RecursiveArrayIterator($array);
		$recursive = new RecursiveIteratorIterator(
			$iterator,
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($recursive as $key => $value) {
			if ($key === $needle) {
				return $value;
			}
		}
	}

	private function checkUserSign(){
		Global $_CFG ;

		// base64 size / 3
		if (strlen($_POST['sign']) / 3 > 50 * 1024){
			$this->output['error'] = 'ntf attach1';
			$this->output['error_text'] = 'Изображение с вашей подписью неправильное';
			return ;
		}

		// опытным путём установлено, что пустая петиция имеет размер в байтах менее 1800
		if (strlen($_POST['sign']) < 1800){
			$this->output['error'] = 'ntf attach1';
			$this->output['error_text'] = 'Изображение с вашей подписью пришло пустым. Обновите страницу и попробуйте, пожалуйста, ещё раз.';
			return ;
		}

		if (strpos($_POST['sign'], 'data:image/png;base64,') === false){
			$this->output['error'] = 'ntf attach2';
			$this->output['error_text'] = 'Изображение с вашей подписью некорректное';
			return ;
		}

		$_POST['sign_md5'] = md5($_POST['sign']);

		if ($_POST['sign_md5'] == '537c1dacb97b2a4a338b1f115743390a'){
			$this->output['error'] = 'ntf attach21';
			$this->output['error_text'] = 'Вы забыли оставить свою подпись';
			return ;
		}

		$tmp_path = $_CFG['root'] . 'cache/images/' . $_POST['sign_md5'] . '.png';

		file_put_contents($tmp_path, base64_decode(substr($_POST['sign'], 22)));

		if(($f_type = getimagesize($tmp_path)) != true){
			$this->output['error'] = 'ntf attach3';
			$this->output['error_text'] = 'Изображение подписи пришло с ошибкой';
			unlink($tmp_path);
			return ;
		}

		if ($f_type[0] != 328 OR $f_type[1] != 125){
			$this->output['error'] = 'ntf attach4';
			$this->output['error_text'] = 'Изображение с вашей подписью некорректное';
			unlink($tmp_path);
			return ;
		}

		unlink($tmp_path);

	}

	private function checkIsActionNotLimitedByIp(){

		$user_ip = GetRealIp();

		if ($user_ip == '195.9.124.202') return ;
		if ($user_ip == '91.77.210.236') return ;
		if ($user_ip == '188.163.109.50') return ;


		// Количество действий с данного айпи за последние 24 часа
		$actionsAmount = db_main_activityLog::count(array(
			'conditions' => array('ip=? AND date>= NOW() - INTERVAL 24 HOUR', $user_ip),
		));

		if ($actionsAmount + 1 > $this->actionsPerDay){
			$this->output['error_text'] = 'Вами достигнут лимит на регистрации с одного IP в сутки' ;
			$this->output['error'] = 'wrong limit';
			return ;
		}

	}



}

$CReg_user_ajax = new CReg_user_ajax;
?>