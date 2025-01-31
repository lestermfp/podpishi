<?php

//require_once 'bootstrap.php';
//require_once('../../config.php');
include('./vendor/autoload.php');

use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun('key-4a3816fa21ad87ed634352cd1e25c626');
$domain = "podpishi.org";

$list = file('./massEmailList.txt');

foreach ($list as $_receiver){

	$_receiver = trim($_receiver);

	$template = 'Добрый день<br/>
Штаб Гудкова ищет людей на постоянную оплачиваемую работу (16 часов в неделю) — раздача листовок на улицах округа.<br/>
Если вам интересно это предложение, ответьте на это письмо, или напишите в телеграм @gudshtab.<br/><br/>

С уважением, Штаб' ;


	$params = array(
		'from'    => 'Gudkov <noreply@podpishi.org>',
		'to'      => $_receiver,
		'subject' => 'Штаб Гудкова ищет людей на постоянную оплачиваемую работу',
		'h:Reply-To' => 'shtab@gudkov.ru',
		'html'    => $template,
	);
		

	try {
		# Make the call to the client.
		$result = $mgClient->sendMessage($domain, $params);
			
		print '<pre>' . print_r($result, true) . '</pre>';	
		
		print 'Is sent ' . $_receiver . '<br/>';
			
		//file_put_contents('./emails_sent/' . $_receiver, $template);	
			
		//$_user->is_mailed = '1';
		//$_user->save();
		
		
	} catch (Exception $e) {
		
		//print '<pre>' . print_r($e, true) . '</pre>';	
		
	}

	//exit();
}

exit();

$users = db_main_users::find('all', array(
	'select' => 'id, email, name, is_mailed',
	'conditions' => array('destination="meriya" AND city="Москва" AND is_mailed=0'),
	'limit' => 50,
));


$template_raw = file_get_contents('./mail_template.html');
$subject = 'Примите участие в кампании по спасению троллейбусов';
	
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