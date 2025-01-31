<?php

$files = scandir('./petitions');


foreach ($files as $_file){
	
	if (strpos($_file, 'large') === false) continue ;
	
	
	print substr($_file, strlen('large_'), -5) . ',';
	
}
?>