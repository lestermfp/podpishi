<?php
include('../config.php');
/*
	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
*/

//if (!isset($_SESSION['flow_authed'])) exit();
	
class CReg_user_ajax extends gdHandlerSkeleton {
    public $output = array(); //Создаем массив который будет отослан в виде JSON.

    public function __construct() {

		$this->output['error'] = 'false';
		
        if (isset($_GET['context'])) {
            switch ($_GET['context']) {

				case 'loadPetitionsV2':
                    $this->loadPetitionsV2();
                    break;
				case 'loadDistrictsV2':
                    $this->loadDistrictsV2();
                    break;
				case 'loadPetitionsFullV2':
                    $this->loadPetitionsFullV2();
                    break;
				case 'petitionReport':
                    $this->petitionReport();
                    break;
				case 'approvePetition':
                    $this->approvePetition();
                    break;
				case 'getPhonesListFromDistrict':
                    $this->getPhonesListFromDistrict();
                    break;
				case 'getAllPhonesList':
                    $this->getAllPhonesList();
                    break;
				case 'getAllEmailsList':
                    $this->getAllEmailsList();
                    break;

					
					

            }
        }
		
		
		print json_encode($this->output);
    }
	

	private function getAllPhonesList(){
		
		print 'ntf';
		exit();
		$users = db_main_users::find('all', array(
			'select' => 'phone, email, name, street_name, house_number, district_name',
		));
		
		//print '<pre>' . print_r($users, true) . '</pre>';
		
		
		$phonesFound = array();
		$list = array();
		
		foreach ($users as $_user){
			
			$phone =  @parseAndformatPhone($_user->read_attribute('phone'));
			
			if (in_array($phone, $phonesFound))
				continue ;
			
			$phonesFound[] = $phone ;
			
			$list[] = array(
				'телефон' => $phone,
				'E-mail' => $_user->read_attribute('email'),
				'ФИО' => $_user->read_attribute('name'),
				'Адрес' => $_user->read_attribute('street_name'). ', ' . $_user->read_attribute('house_number'),
				'Район' => $_user->read_attribute('district_name'),
				
			);
		}
		
		//foreach ($phones as $_phone)
		//	print $_phone . '<br/>';
		
		//print json_encode($phones);
		
		$html =  gdHandlerSkeleton::generateSimpleHtmlTable($list);
		
		print $html ;
		
		exit();
		
		print '<pre>' . print_r($users, true) . '</pre>';
		
	}
	
	private function getAllEmailsList(){
		
		$users = db_main_users::find('all', array(
			'select' => 'phone, email, name, street_name, house_number, district_name',
		));
		
		//print '<pre>' . print_r($users, true) . '</pre>';
		
		
		$emailsFound = array();
		$list = array();
		
		foreach ($users as $_user){
			
			$email =  ($_user->read_attribute('email'));
			
			if (in_array($email, $emailsFound))
				continue ;
			
			$emailsFound[] = $email ;
			
			$list[] = array(
				'E-mail' => $_user->read_attribute('email'),
				
			);
		}
		
		//foreach ($phones as $_phone)
		//	print $_phone . '<br/>';
		
		//print json_encode($phones);
		
		$html =  gdHandlerSkeleton::generateSimpleHtmlTable($list);
		
		print $html ;
		
		exit();
		
		print '<pre>' . print_r($users, true) . '</pre>';
		
	}
	
	
	private function getPhonesListFromDistrict(){
		
		
		$users = db_main_users::find('all', array(
			'select' => 'phone, email, name, street_name, house_number, district_name',
			'conditions' => array('lower(district_name) LIKE "%щукино%" OR lower(district_name) LIKE "%строгино%" OR lower(district_name) LIKE "%митино%" OR lower(district_name) LIKE "%покровское%" OR lower(district_name) LIKE "%мневники%" OR lower(district_name) LIKE "%южное тушино%"'),
		));
		
		//print '<pre>' . print_r($users, true) . '</pre>';
		
		
		$phones = array();
		
		foreach ($users as $_user)
			$phones[] = parseAndformatPhone($_user->read_attribute('phone'));
		
		foreach ($phones as $_phone)
			print $_phone . '<br/>';
		
		//print json_encode($phones);
		
		exit();
		
		print '<pre>' . print_r($users, true) . '</pre>';
		
	}
	
	private function loadDistrictsV2(){
		
		
		$reply = db_main_users::find_by_sql('SELECT count(*) as amount, district_name FROM `main_users` WHERE district_name!="" GROUP by district_name');
		
		//print '<pre>' . print_r($reply, true) . '</pre>';
		
		$this->output['table'] = array();
		
		foreach ($reply as $_entry){
			
			$newRow = $_entry->to_array();
			
			$this->output['table'][] = $newRow ;
		}
		
	}
	
	/*
		Arg: print_session_name	
	*/
	private function petitionReport(){

		if (!isset($_GET['print_session_name']) OR empty($_GET['print_session_name'])){
			print 'Wrong ';
			exit();
		}
	
		$users = db_main_users::find('all', array(
			'conditions' => array("print_session_name=?", $_GET['print_session_name']),
			'order' => 'deputy_name ASC',
		));
		
		print '<meta content="text/html; charset=utf-8" http-equiv="Content-Type">';
		print 'Количество граждан: ' . count($users) . '<br />';



		
		print '<table border=1>';
		print '<tr><td>id</td><td>ФИО</td><td>Телефон</td><td>E-mail</td><td>Депутат</td><td>Дата</td><td>Текст</td></tr>';
		
		foreach ($users as $_user){
			print '<tr><td>' . $_user->read_attribute('id') . '</td><td>' . $_user->read_attribute('name_ncl') . '</td><td>' . $_user->read_attribute('phone') . '</td><td>' . $_user->read_attribute('email') . '</td><td>' . $_user->read_attribute('deputy_name') . '</td><td>' . $_user->read_attribute('reg_date')->format('d.m.y H:i') . '</td><td></td></tr>';
		}
		
		print '</table>';		
		print '<h2>Результаты</h2>';
		
		$files = scandir('./api/petitions_combined');
		
		$i = 0;
		foreach ($files as $_file){
			
			if (strpos($_file, $_GET['print_session_name']) === false) continue ;
			
			$i++ ;
			print $i . ') <a href="https://podpishi.org/ajax/api/petitions_combined/' . $_file . '">' . $_file . '</a><br/>';
			
			
		}
		
		print 'Готовность: ' . round($i / (ceil(count($users) / 50)) * 100) . '%';
		
		exit();
	}
	
	private function approvePetition(){
		
		
		$user = db_main_users::find_by_id($_GET['user_id']);
		
		if (empty($user)){
			
			$this->output['error'] = 'error';
			$this->output['error_text'] = 'error';
			return ;
		}
		
		if ($user->read_attribute('is_approved') == 1){
			
			$this->output['error'] = 'error';
			$this->output['error_text'] = 'Петиция и так заапрувлена';
			return ;
		}
		
		
		$user->is_approved = 1 ;
		$user->save();
		
		
	}
	
	private function loadPetitionsFullV2(){
		Global $_CFG ;
		
		$defaultSelectRange = '*';
	
		$users = db_main_users::find('all', array(
			'select' => $defaultSelectRange,
			'order' => 'id DESC',
			'conditions' => 'is_manual_text=1 AND is_approved=1'
		));		
		
		$this->output['full_texts'] = array();

		$texts = array();
		
		foreach ($_CFG['petition_text'] as $_key => $_text){
			
			$texts[$_key] = $this->parseWords($_text);
			
		}
		

		
		
		

		foreach ($users as $_user){
			
			$is_manual_text = $_user->read_attribute('is_manual_text');
			
			if ($is_manual_text == 1){
				$is_manual_text = '<div class="btn btn-primary" onClick="flow.showFullPetition(this);">Читать</div>';
			}
			else {
				$is_manual_text = 'обычный';
			}
			
			
			$text_new = $this->parseWords($_user->read_attribute('petition_text'));
			
			$text_diff = $this->returnDifference($texts[$_user->read_attribute('destination')], $text_new);
			
			
			if ($text_diff['diff'] < 10) continue ;
			
			//print '<pre>' . print_r($text_new, true) . '</pre>';
			//print '<pre>' . print_r($texts[$_user->read_attribute('destination')], true) . '</pre>';
			
			//print '<pre>' . print_r($texts, true) . '</pre>';
			//exit();
			/*
			$strlen_original = strlen($_CFG['petition_text'][$_user->read_attribute('destination')]);
			$strlen_new = strlen($_user->read_attribute('petition_text'));

			$difference = abs($strlen_original - $strlen_new);
			
			if ($difference < 10) continue ; 
			*/
			
			$newRow = array (
				'id' => $_user->read_attribute('id'),
				'name_ncl' => $_user->read_attribute('name_ncl'),
				'full_textfull_textfull_textfull_textfull_textfull_textfull_textfull_textfull_text' => '',
				'email' => $_user->read_attribute('email'),
				'phone' => $_user->read_attribute('phone'),
				'address' => $_user->read_attribute('city') . ' '  . $_user->read_attribute('street_name') . ' '  . $_user->read_attribute('house_number') . ' '  . $_user->read_attribute('appartment'),
				'deputy_name' => $_user->read_attribute('deputy_name'),
				'destination' => $_user->read_attribute('destination'),
				' sign_of_citizen ' => '<img width="150" src="' . $_user->read_attribute('sign') . '">',
				'date' => $_user->read_attribute('reg_date')->format('d.m.Y'),
			);
			
			if ($_user->read_attribute('is_manual_text') == 1){
				$petition_text = str_replace("\n", '<br/>', $_user->read_attribute('petition_text'));
				
				foreach ($text_diff['unique_words'] as $_word){
					$petition_text = str_replace($_word, '<i style="background: rgba(0, 0, 255, 0.28);">' . $_word . '</i>', $petition_text);
				}
				
				$newRow['full_textfull_textfull_textfull_textfull_textfull_textfull_textfull_textfull_text'] = $petition_text ;
			}
			
			$this->output['table'][] = $newRow ;
			
		}
		
	
		
		//print '<pre>' . print_r($this->output, true) . '</pre>';
		

		
	}

	private function loadPetitionsV2(){
		Global $_CFG ;
		
		$defaultSelectRange = '*';
	
		$users = db_main_users::find('all', array(
			'select' => $defaultSelectRange,
			'order' => 'id DESC',
			'conditions' => 'is_manual_text=1'
		));		
		
		$this->output['full_texts'] = array();

		$texts = array();
		
		foreach ($_CFG['petition_text'] as $_key => $_text){
			
			$texts[$_key] = $this->parseWords($_text);
			
		}
		

		
		
		

		foreach ($users as $_user){
			
			$is_manual_text = $_user->read_attribute('is_manual_text');
			
			if ($is_manual_text == 1){
				$is_manual_text = '<div class="btn btn-primary" onClick="flow.showFullPetition(this);">Читать</div>';
			}
			else {
				$is_manual_text = 'обычный';
			}
			
			
			$text_new = $this->parseWords($_user->read_attribute('petition_text'));
			
			$text_diff = $this->returnDifference($texts[$_user->read_attribute('destination')], $text_new);
			
			
			if ($text_diff['diff'] < 10) continue ;
			
			//print '<pre>' . print_r($text_new, true) . '</pre>';
			//print '<pre>' . print_r($texts[$_user->read_attribute('destination')], true) . '</pre>';
			
			//print '<pre>' . print_r($texts, true) . '</pre>';
			//exit();
			/*
			$strlen_original = strlen($_CFG['petition_text'][$_user->read_attribute('destination')]);
			$strlen_new = strlen($_user->read_attribute('petition_text'));

			$difference = abs($strlen_original - $strlen_new);
			
			if ($difference < 10) continue ; 
			*/
			
			$newRow = array (
				'id' => $_user->read_attribute('id'),
				'name_ncl' => $_user->read_attribute('name_ncl'),
				'email' => $_user->read_attribute('email'),
				'phone' => $_user->read_attribute('phone'),
				'address' => $_user->read_attribute('city') . ' '  . $_user->read_attribute('street_name') . ' '  . $_user->read_attribute('house_number') . ' '  . $_user->read_attribute('appartment'),
				'kv_raw' => $_user->read_attribute('appartment_raw'),
				'deputy_name' => $_user->read_attribute('deputy_name'),
				'destination' => $_user->read_attribute('destination'),
				' sign_of_citizen ' => '<img width="150" src="' . $_user->read_attribute('sign') . '">',
				'is_manual_text' => $is_manual_text,
				'is_sent' => $_user->read_attribute('is_sent'),
				'is_approved' => $_user->read_attribute('is_approved'),
				'is_on_trolley' => $_user->read_attribute('is_on_trolley'),
				'date' => $_user->read_attribute('reg_date')->format('d.m.Y'),
			);
			
			if ($_user->read_attribute('is_manual_text') == 1){
				$petition_text = str_replace("\n", '<br/>', $_user->read_attribute('petition_text'));
				
				foreach ($text_diff['unique_words'] as $_word){
					$petition_text = str_replace($_word, '<b>' . $_word . '</b>', $petition_text);
				}
				
				$this->output['full_texts'][$_user->read_attribute('id')] = $petition_text;
				
			}
			
			$this->output['table'][] = $newRow ;
			
		}
		
	
		
		//print '<pre>' . print_r($this->output, true) . '</pre>';
		

		
	}
	
	private function returnDifference($old, $new){
		
		$diff = 0 ;
		$unique_words = array();
		
		foreach ($new as $_word => $_amount)
			if (!isset($old[$_word])){
				$diff++;
				$unique_words[] = $_word ;
				//print 'Unique ' . $_word . '<br/>';
			}
	
		return array('diff' => $diff, 'unique_words' => $unique_words) ;
	}
	
	private function parseWords($_text){
		$_text = str_replace(array("\n", '\n'), ' ', $_text);
		$_text = str_replace(array('«','.', '»','\n'), '', $_text);
		
		$words = explode(' ', $_text);
		
		$texts = array();
		
		foreach ($words as $_word){
			
			$_word = trim($_word);
			
			if (strlen($_word) < 5) continue ;
			
			if (!isset($texts[$_word]))
				$texts[$_word] = 0;
			
			$texts[$_word]++ ;
			
		}		
		
		return $texts ;
	}

}

$CReg_user_ajax = new CReg_user_ajax;
?>