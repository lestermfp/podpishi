<?php

	ini_set('display_errors', true);
	ini_set('error_reporting',  E_ALL);
	error_reporting(E_ALL + E_STRICT);
	
$path_to_combiner = '/var/www/podpishi.org/ajax/api/wkhtmltox/bin/wkhtmltopdf';
$template_raw = file_get_contents('./html/index2.html');

$generated_name = md5(serialize($_POST)) . date('d_m_Y_H_i_s');

$_POST['plot_text1251'] = iconv('utf-8', 'windows-1251', $_POST['plot_text']);
$_POST['district1251'] = iconv('utf-8', 'windows-1251', $_POST['district']);
$_POST['birthdate1251'] = iconv('utf-8', 'windows-1251', $_POST['birthdate']);
$_POST['address1251'] = iconv('utf-8', 'windows-1251', $_POST['address']);
$_POST['member_name1251'] = iconv('utf-8', 'windows-1251', $_POST['member_name']);



$gender_smog_obyasnit = 'смог объяснить';
$gender_soglasen = 'согласен';
$gender_imel = 'имел представления';
$gender_demonstrate = 'Демонстрировал';
$gender_zayavil = 'Заявил';
$gender_not_known = 'не знал';
$gender_oznak = 'ознакомился';
if ($_POST['gender'] == 'female'){
	$gender_soglasen = 'согласна';
	$gender_smog_obyasnit = 'смогла объяснить';
	$gender_imel = 'имела представления';
	$gender_demonstrate = 'Демонстрировала';
	$gender_zayavil = 'Заявила';
	$gender_not_known = 'не знала';
	$gender_oznak = 'ознакомилась';
}


$gender_soglasen = iconv('utf-8', 'windows-1251', $gender_soglasen);
$gender_imel = iconv('utf-8', 'windows-1251', $gender_imel);
$gender_zayavil = iconv('utf-8', 'windows-1251', $gender_zayavil);
$gender_demonstrate = iconv('utf-8', 'windows-1251', $gender_demonstrate);
$gender_smog_obyasnit = iconv('utf-8', 'windows-1251', $gender_smog_obyasnit);
$gender_not_known = iconv('utf-8', 'windows-1251', $gender_not_known);



$main_reason = '<span style=\'font-size:12.0pt;
font-family:"Times New Roman","serif";mso-fareast-font-family:"Times New Roman";
color:black;mso-fareast-language:RU\'>Об отказе в приёме в партию мне стало известно от третьих лиц, на указанный в заявлении адрес электронной почты мне письмо не высылалось, иными способами со мной не связывались. С решением Регионального Совета об отказе в приёме в партию группе лиц я ' . $gender_oznak . ' на сайте Московского регионального отделения
</span>';

if (isset($_POST['main_reason']) AND !empty($_POST['main_reason']) AND $_POST['main_reason'] != 'none'){
	$main_reason = '<span style=\'font-size:12.0pt;
	font-family:"Times New Roman","serif";mso-fareast-font-family:"Times New Roman";
	color:black;mso-fareast-language:RU\'>Об отказе в приёме в партию мне стало
	известно из электронного письма, поступившего на мою электронную почту с адреса
	</span><span style=\'font-size:12.0pt\'><a href="mailto:' . $_POST['main_reason'] . '"><span
	style=\'font-family:"Times New Roman","serif";mso-fareast-font-family:"Times New Roman";
	color:#1155CC;mso-fareast-language:RU\'>' . $_POST['main_reason'] . '</span></a></span><span
	style=\'font-size:12.0pt;font-family:"Times New Roman","serif";mso-fareast-font-family:
	"Times New Roman";color:black;mso-fareast-language:RU\'> . В письме не указано,
	кому оно было отправлено, моё имя в письме не упоминается. </span>';	
}

$main_reason = iconv('utf-8', 'windows-1251', $main_reason);



if (!isset($_POST['spawn'])){
	$_POST['spawn'] = '.';
	$_POST['spawn_orig'] = 'нет';
}
else {
	$_POST['spawn_orig'] = $_POST['spawn'];
	$_POST['spawn'] = iconv('utf-8', 'windows-1251', ', первичном отделении ') . $_POST['spawn'];
}

$template_raw = str_replace('{plot_text}', '<u>' . $_POST['plot_text1251'] . '</u>', $template_raw);
$template_raw = str_replace('{spawn}', $_POST['spawn'], $template_raw);
$template_raw = str_replace('{member_name}', $_POST['member_name1251'], $template_raw);
$template_raw = str_replace('{birthdate}', $_POST['birthdate1251'], $template_raw);
$template_raw = str_replace('{address}', $_POST['address1251'], $template_raw);
$template_raw = str_replace('{gender_soglasen}', $gender_soglasen, $template_raw);
$template_raw = str_replace('{gender_smog_obyasnit}', $gender_smog_obyasnit, $template_raw);
$template_raw = str_replace('{gender_imel}', $gender_imel, $template_raw);
$template_raw = str_replace('{main_reason}', $main_reason, $template_raw);
$template_raw = str_replace('{gender_zayavil}', $gender_zayavil, $template_raw);
$template_raw = str_replace('{gender_demonstrate}', $gender_demonstrate, $template_raw);
$template_raw = str_replace('{gender_not_known}', $gender_not_known, $template_raw);
$template_raw = str_replace('{district}', str_replace('.', '', $_POST['district1251']), $template_raw);


$template_raw = preg_replace('/^<\?php(.*)(\?>)?$/s', '$1', $template_raw);


	$save_path = './html_generated/large_' . $generated_name . '.html';
	file_put_contents($save_path, $template_raw);

// convert html to pdf
$pdf_dest = ' ./pdf_generated/' . $generated_name . '.pdf';
$cmd = $path_to_combiner . ' ' . $save_path . $pdf_dest;

system($cmd);

$output = array(
	'error' => 'false',
	'href' => '/static/form/pdf_generated/' . $generated_name . '.pdf',
);

$botUrl = 'https://podpishi.org/static/form/tg/tg_appleFormLive.php?new_appleForm&district=' . $_POST['district'] . '&generated_name=' . $generated_name . '&spawn=' . $_POST['spawn_orig'] . '&birthdate=' . $_POST['birthdate'] . '&address=' . $_POST['address'] . '&member_name=' . $_POST['member_name'];
file_get_contents($botUrl);

$log_path = './unique_names/' . md5($_POST['member_name']);

if (!file_exists($log_path)){
	file_put_contents($log_path, serialize(array(
		'post' => $_POST,
		'generated_name' => $generated_name,
	)));
}

print json_encode($output);

?>