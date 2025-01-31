<?php
//////////////////////////////////////////////////
//
// Список значений принимаемый через POST запрос:
// name - имя
// last_name - фамилия
// second_name - отчество
// phone - телефон
// email - эл. почта
// address - адрес
// source_description - описание источника, строка
// utm_campaign - код компании
//
// Поля по-умолчанию:
// opened - флаг доступности контакта всем пользователеям
// export - флаг возможности экспорта, иначе данные контакта невозможно экспортировать
//

$data = array('NAME' => htmlspecialchars($_POST['name']),
		'LAST_NAME' => htmlspecialchars($_POST['last_name']),
		'SECOND_NAME' => htmlspecialchars($_POST['second_name']),
		'PHONE' => array('n0' => array('VALUE' => htmlspecialchars($_POST['phone']), 'VALUE_TYPE' => 'MAILING')),
		'EMAIL' => array('n0' => array('VALUE' => htmlspecialchars($_POST['email']), 'VALUE_TYPE' => 'MAILING')),
		'ADDRESS' => htmlspecialchars($_POST['address']),
		'SOURCE_DESCRIPTION' => htmlspecialchars($_POST['source_description']),
		'UTM_CAMPAIGN' => htmlspecialchars($_POST['utm_campaign']),
		'OPENED' => 'Y',
		'EXPORT' => 'Y');

//print '<pre>' . print_r($data, true) . '</pre>';

$result = send_to_bx($data);

var_dump($result);

function send_to_bx($data){
	$bx_url = 'https://yabloko.bitrix24.ru/rest/35/dunjsbhiyqpsntc0/crm.contact.add.json';
	//здесь нужно указать url входящего вэбхука для метода crm.contact.add пример:
	//https://xxx.bitrix24.ru/rest/1/xxxxxxxxxxxxxxxx/crm.contact.add.json

	$data_post = array('fields' => $data);
	//var_dump($data_post);
	//убрать комментарий для просмотра данных перед отправкой
	$options = array(
	    'http' => array(
	        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
	        'method' => 'POST',
	        'content' => http_build_query($data_post)
	    )
	);
	$context = stream_context_create($options);
	$result = file_get_contents($bx_url, false, $context);
	//var_dump($result);
	//убрать комментарий для просмотра результата запроса
	return $result;
}
?>
