<?php

// Подключение библиотеки Mandrill
require_once ('Mandrill.php');

$mail = new Mandrill('j7VYM-pwpCPpf3RGdPvGEw');

$data = array(
	'html' => '',
	'text' => '',
	'subject' => 'Print',
	'from_email' => 'webmaster@gudkov.ru',
	'from_name' => 'Podpishi',
	'to' => array(		
		array(
			'email' => 'viksem999@gmail.com',
			'name' => 'Viktor',
			'type' => 'to'
		)
	),
	'attachments' => array(
		array(
			'type' => 'text/html',
			'name' => 'html_to_print.html',
			'content' => base64_encode(file_get_contents('html_to_print.html')),
		)
	),
);

try {
	/* Отправка обычного письма */
	$result = $mail->messages->send($data);

	print '<pre>' . print_r($result, true) . '</pre>';

} catch(Mandrill_Error $error) {
	echo 'Error: ' . get_class($error) . ' - ' . $error->getMessage();
}



/*
	'attachments' => array(
		array(
			'type' => 'application/pdf',
			'name' => 'test.pdf',
			'content' => base64_encode(file_get_contents('test.pdf'))
		)
	),
	'images' => array(
		array(
			'type' => 'image/png',
			'name' => 'Smiley.png',
			'content' => base64_encode(file_get_contents('Smiley.png'))
		)
	),
	'subaccount' => 'cust-123'
*/


?>