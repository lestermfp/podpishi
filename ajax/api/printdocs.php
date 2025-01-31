<?php

//require_once 'bootstrap.php';
require_once('../../config.php');
require $_CFG['root'] . 'ajax/api/ncl/NCL.NameCase.ru.php';
include('./vendor/autoload.php');

if (!isset($_GET['debug'])){
	//print 'debug';
	//exit();
}

if (file_exists('./printdocs_started')){
	print 'Locked';
	exit();
}



file_put_contents('./printdocs_started', 'true');

$path_to_config = $_CFG['root'] . 'config/print_mode.nfo';
$path_to_current_session = $_CFG['root'] . 'config/current_session.nfo';
$path_to_combiner = '/var/www/podpishi.org/ajax/api/wkhtmltox/bin/wkhtmltopdf';

$current_mode = file_get_contents($path_to_config);
$print_session_name = file_get_contents($path_to_current_session);

if (empty($current_mode) OR empty($print_session_name)){
	print 'wrong mode';
	unlink('./printdocs_started');
	exit();
}

/*
print 'print_session_name ' . $print_session_name . '<br/>';
print 'current_mode ' . $current_mode . '<br/>';
exit();
*/

// Подключение библиотеки Mandrill
//require_once ('Mandrill.php');

//$mail = new Mandrill('j7VYM-pwpCPpf3RGdPvGEw');

use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun('key-4a3816fa21ad87ed634352cd1e25c626');
$domain = "mcdonate.ru";


// 'conditions' => array('(is_approved=1 AND destination=? AND is_sent=0 AND queue_date <= NOW() - INTERVAL 30 MINUTE) OR id=-1', $current_mode),
$entries = db_main_users::find('all', array(
	'conditions' => array('destination=? AND print_session_name=? AND is_sent=0', $current_mode, $print_session_name),
	'order' => 'deputy_name ASC',
	'limit' => 50,
));


//if (count($entries) < 8) exit();

$template_raw = file_get_contents('./template_to_combine.html');
$monthsList = array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

$attachments = array();
$list_ids = array();

foreach ($entries as $_entry){
	
	$template = $template_raw ;
	
	$list_ids[] = $_entry->read_attribute('id');
	
	$address_text = $_entry->read_attribute('city') . ', ' . $_entry->read_attribute('street_name') . ' ' . $_entry->read_attribute('house_number');
	
	if ($_entry->read_attribute('appartment') != 0){
		$address_text = $address_text. ' кв. ' . $_entry->read_attribute('appartment');
	}
	
	
	//$petition_text = "</p><p class=MsoNormal style='text-align:justify;text-justify:inter-ideograph;text-indent:35.4pt'>" . $_entry->read_attribute('petition_text');
	$petition_text = "<p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;text-align: justify;text-justify:inter-ideograph;text-indent:35.4pt;line-height:140%'><span style='font-size:10.5pt;mso-bidi-font-size:11.5pt;line-height:140%;font-family: \"Times New Roman\",\"serif\"'>" . $_entry->read_attribute('petition_text');

	$petition_text = str_replace("<br />", '\n', nl2br($petition_text));
	
	//$petition_text = str_replace('\n', "</p><p class=MsoNormal style='text-align:justify;text-justify:inter-ideograph;
//text-indent:35.4pt'>", $petition_text);
	$petition_text = str_replace('\n', "</span><span style='font-size:9.0pt;mso-bidi-font-size:11.5pt;line-height:140%'><o:p></o:p></span></p><p class=MsoNormal style='margin-bottom:0cm;margin-bottom:.0001pt;text-align: justify;text-justify:inter-ideograph;text-indent:35.4pt;line-height:140%'><span style='font-size:10.5pt;mso-bidi-font-size:11.5pt;line-height:140%;font-family: \"Times New Roman\",\"serif\"'>", $petition_text);

	$author_name = $_entry->read_attribute('name_ncl');

	$template = str_replace('{author}', $author_name, $template);
	$template = str_replace('{address_text}', $address_text, $template);
	$template = str_replace('{petition_text}', $petition_text, $template);
	$template = str_replace('{date_day}', $_entry->read_attribute('reg_date')->format('d'), $template);
	$template = str_replace('{date_month}', $monthsList[$_entry->read_attribute('reg_date')->format('n')], $template);
	$template = str_replace('{date_year}', $_entry->read_attribute('reg_date')->format('Y'), $template);
	$template = str_replace('{user_sign}', $_entry->read_attribute('sign'), $template);
	
	if ($_entry->read_attribute('phone') == ''){
		$template = str_replace('{phone}', '', $template);
	}
	else {
		$template = str_replace('{phone}', 'Телефон: ' . $_entry->read_attribute('phone'), $template);
	}
	
	if (in_array($_entry->read_attribute('destination'), array('mosgorduma', 'mosgorduma_velo'))){
		
		$firstWord = array(
			'm' => 'Уважаемый ',
			'f' => 'Уважаемая ',
		);
		
		# Объявляем объект класса.
		$case = new NCLNameCaseRu();

		# Метод q - склоняет Фамилию, Имя и Отчество человека по правилам пола.
		$array_ncl = $case->q($_entry->read_attribute('deputy_name'));
		//Депутату Московской городской думы <br/ > ' + petition.msg.deputy.name_ncl[2] + '<br/>
		
		$whom_header_top = 'Депутату Московской городской Думы<br>
<span class=SpellE>' . $array_ncl[2] . '</span>';

		$name_parts = array();
		list($name_parts['surname'], $name_parts['name'], $name_parts['middlename']) = explode(' ', $array_ncl[0]);

		$whom_header_petition = $firstWord[$_entry->read_attribute('deputy_sex')] . $name_parts['name'] . ' ' . $name_parts['middlename'];
		
		$template = str_replace('{target_name}', $firstWord[$_entry->read_attribute('deputy_sex')] . $name_parts['name'] . ' ' . $name_parts['middlename'] . '!', $template);
		
		
		
	}
	
	if ($_entry->read_attribute('destination') == 'gosduma'){
		
		$firstWord = array(
			'm' => 'Уважаемый ',
			'f' => 'Уважаемая ',
		);
		
		# Объявляем объект класса.
		$case = new NCLNameCaseRu();

		# Метод q - склоняет Фамилию, Имя и Отчество человека по правилам пола.
		$array_ncl = $case->q($_entry->read_attribute('deputy_name'));
		//Депутату Московской городской думы <br/ > ' + petition.msg.deputy.name_ncl[2] + '<br/>
		
		$whom_header_top = 'Депутату Государственной Думы<br>
<span class=SpellE>' . $array_ncl[2] . '</span>';

		$name_parts = array();
		list($name_parts['surname'], $name_parts['name'], $name_parts['middlename']) = explode(' ', $array_ncl[0]);

		$whom_header_petition = $firstWord[$_entry->read_attribute('deputy_sex')] . $name_parts['name'] . ' ' . $name_parts['middlename'];
		
		$template = str_replace('{target_name}', $firstWord[$_entry->read_attribute('deputy_sex')] . $name_parts['name'] . ' ' . $name_parts['middlename'] . '!', $template);
		
		
		
	}
	
	if (in_array($_entry->read_attribute('destination'), array('meriya', 'meriya_velo'))){
		$whom_header_top = 'Мэру Москвы<br>
С.С. <span class=SpellE>Собянину</span>';
		$whom_header_petition = 'Уважаемый Сергей Семёнович';
	}
	
	$template = str_replace('{whom_header_top}', $whom_header_top, $template);
	$template = str_replace('{whom_header_petition}', $whom_header_petition, $template);

	$save_path = './petitions/large_' . $_entry->read_attribute('id') . '.html';
	file_put_contents($save_path, $template);

	//continue ;
	//if ($_entry->read_attribute('destination') == 'mosgorduma')
		//continue ;
	
	
	$attachments[] = $save_path;
	
	//continue ;

	//print 'wtf';
	
	$_entry->is_sent = '1';
	$_entry->save();
	
	continue ;
	
	$data = array(
		'html' => '',
		'text' => '',
		'subject' => 'Print #' . $_entry->read_attribute('id'),
		'from_email' => 'webmaster@gudkov.ru',
		'from_name' => 'Podpishi',
		'to' => array(		
			array(
				'email' => 'oky88uba793@hpeprint.com',
				'name' => 'HP',
				'type' => 'to'
			)
		),
		'attachments' => array(
			array(
				'type' => 'text/html',
				'name' => $_entry->read_attribute('id') . '.html',
				'content' => base64_encode($template),
			)
		),
	);

	try {
		/* Отправка обычного письма */
		$result = $mail->messages->send($data);

		print '<pre>' . print_r($result, true) . '</pre>';
			
		$_entry->is_sent = '1';
		$_entry->save();
		
		//file_put_contents($_entry->read_attribute('id') . '.html', iconv('utf-8', 'windows-1251', $template));

	} catch(Mandrill_Error $error) {
		echo 'Error: ' . get_class($error) . ' - ' . $error->getMessage();
	}


	
	
	//print '<pre>' . print_r($_entry, true) . '</pre>';
	//continue ;


}


// combine many files into one pdf
$pdf_dest = ' ./petitions_combined/print_' . $print_session_name . '_' . date('d_m_Y_H_i_s') . '.pdf';
$cmd = $path_to_combiner . ' ' . implode(' ',$attachments) . $pdf_dest;
//print $cmd ;

system($cmd);
unlink('./printdocs_started');
exit();

$attachments = array($pdf_dest);

$params = array(
	'from'    => 'MCDonate.ru <support@mcdonate.ru>',
	'to'      => 'oky88uba793@hpeprint.com',
	'subject' => 'Print #' . $_entry->read_attribute('id'),
	'html'    => ' ',
);
	

	
try {
	# Make the call to the client.
	$result = $mgClient->sendMessage($domain, $params, array(
		'attachment' => $attachments,
	));
		
	print '<pre>' . print_r($result, true) . '</pre>';	
		
	//$_entry->is_sent = '1';
	//$_entry->save();
	
	
} catch (Exception $e) {

}	



?>