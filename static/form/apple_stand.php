<?php

file_put_contents('./apple', 'wtf1');

function generateSimpleHtmlTable ($array){
	
	$html = '<meta content="text/html; charset=utf-8" http-equiv="Content-Type"><table border="1">';
	
	foreach ($array as $_key => $_item){
		
		
		if ($_key == 0){
			$html .= '<thead><tr>';
			
			foreach ($_item as $_name => $_value)
				$html .= '<th>' . $_name . '</th>';				
			
			$html . '</thead><tbody>';
		}
		
		$html .= '<tr>';
		foreach ($_item as $_name => $_value)
			$html .= '<td>' . $_value . '</td>';
		
		$html .= '</tr>';
		
		if ($_key == 0)
			$html .= '</tbody>';
		
	}
	
	$html .= '</table>';
	
	
	return $html ;
}

//print '<pre>' . print_r($_SERVER, true) . '</pre>';

$dirs = scandir('./unique_names/');

$outputTable = array();
$number = 1 ;

foreach ($dirs as $_file){
	
	if ($_file == '.' OR $_file == '..')
		continue ;
	
	$content = unserialize(file_get_contents('./unique_names/' . $_file));
	
	if ($content['post']['main_reason'] == 'none')
		$content['post']['main_reason'] = 'не извещали';
	
	
	$outputTable[] = array(
		'№' => $number,
		'ФИО' => $content['post']['member_name'],
		'способ извещения об отказе' => $content['post']['main_reason'],
		'_file' => '<a data-file="' . $_file . '" target="_blank" href="https://podpishi.org/static/form/pdf_generated/' . $content['generated_name'] . '.pdf">PDF</a>',
		
	);
	
	$number++;
}

$html = generateSimpleHtmlTable($outputTable);

print $html ;
exit();

//print '<pre>' . print_r($content, true) . '</pre>';
//exit();


$text = "Всего %s\r\n\r\n";

$text = sprintf($text, count($unique_names));

foreach ($unique_names as $_key => $_name)
	$text .= ($_key + 1) . '. ' . $_name . "\r\n";
	
	
print $text ;

?>