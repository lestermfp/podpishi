<?php

//require_once 'bootstrap.php';
require_once('../../config.php');
require $_CFG['root'] . 'ajax/api/ncl/NCL.NameCase.ru.php';
include('./vendor/autoload.php');

use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun('key-4a3816fa21ad87ed634352cd1e25c626');
$domain = "podpishi.org";

/*

$users = db_main_users::find('all', array(
	'select' => 'id, email, name, is_mailed',
	'conditions' => array('destination="meriya" AND city="Москва" AND is_mailed=0'),
	'limit' => 50,
));
*/

$template_raw = file_get_contents('./meeting_letter.html');
$subject = 'Митинг в защиту троллейбуса 29 января 14:00';
	
$recepients = explode(',', 'viksem999@gmail.com');

$template_raw = str_replace('{subject}', $subject, $template_raw);
exit();


//foreach ($recepients as $_to){
	$params = array(
		'from'    => 'Podpishi.org <noreply@podpishi.org>',
		'to'      => 'podpishi_meeting2901@podpishi.org',
		'subject' => $subject,
		'html'    => $template_raw,
	);
		
	//print $_to . '<br/>';
	
		//3006_callers@mailing.gudkov.ru
	$result = $mgClient->sendMessage($domain, $params);	
//}
		
		//$result = $mgClient->sendMessage($domain, $params);
exit();

$template_raw = file_get_contents('./mail_template_except.html');
$subject = 'направьте запрос о троллейбусах в Госдуму';
	
$template_raw = str_replace('{subject}', $subject, $template_raw);

	$params = array(
		'from'    => 'Комитет «Москвичи за троллейбус» <trolley@city4people.ru>',
		'to'      => 'except_regions@podpishi.org',
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
exit();
	$params = array(
		'from'    => 'Комитет «Москвичи за троллейбус» <trolley@city4people.ru>',
		'to'      => 'theshtab@gmail.com',
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
		
	$params = array(
		'from'    => 'Комитет «Москвичи за троллейбус» <trolley@city4people.ru>',
		'to'      => 'ipravnichenko@gmail.com',
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
			exit();	
	$params = array(
		'from'    => 'Штаб Гудкова: вакансия <shtab@gudkov.ru>',
		'to'      => '9625609@gmail.com',
		'subject' => $subject,
		'h:Reply-To' => 'shtab@gudkov.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
		exit();
	$params = array(
		'from'    => 'Москвичи за троллейбус <trolley@city4people.ru>',
		'to'      => 'viksem999@gmail.com',
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
		
	$params = array(
		'from'    => 'Москвичи за троллейбус <trolley@city4people.ru>',
		'to'      => 'tumkir@gmail.com',
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template_raw,
	);
		
		
		$result = $mgClient->sendMessage($domain, $params);
		
exit();
foreach ($users as $_user){	

	$template = $template_raw ;
	
	$template = str_replace('{receiver}', $_user->read_attribute('email'), $template);
	$template = str_replace('{invite_code}', md5($_user->read_attribute('email') . 'wtf'), $template);
	$template = str_replace('{subject}', $subject, $template);

	$params = array(
		'from'    => 'Podpishi.org <noreply@podpishi.org>',
		'to'      => $_user->read_attribute('email'),
		'subject' => $subject,
		'h:Reply-To' => 'trolley@city4people.ru',
		'html'    => $template,
	);
		
		
	try {
		# Make the call to the client.
		$result = $mgClient->sendMessage($domain, $params);
			
		print '<pre>' . print_r($result, true) . '</pre>';	
			
		file_put_contents('./emails_sent/' . $_user->read_attribute('email'), $template);	
			
		$_user->is_mailed = '1';
		$_user->save();
		
		
	} catch (Exception $e) {

	}
	
	//exit();

}
	
	

exit();

$attachments = array($pdf_dest);



	
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