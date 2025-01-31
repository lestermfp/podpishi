<?php

$_CFG['headers'] = array(
	'1' => array(
		'header' => 'modules/site_header2.php',
		'footer' => 'modules/site_footer.php',
	),
	'2' => array(
		'header' => 'modules/site_header2.php',
		'footer' => 'modules/site_footer.php',
	),
	'3' => array(
		'header' => 'modules/site_header2.php',
		'footer' => 'modules/site_footer.php',
	),
);

$_CFG['modules'] = array
(
    "404" => array
    (
        "url"  => "/not_found/",         // Regular expression to check urls
        "file" => "modules/404.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
		"auth_required" => false,
    ),
    "index" => array
    (
        "url"  => "/index/",         // Regular expression to check urls
        "file" => "modules/index_page.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
		"auth_required" => false,
    ),
    "flow" => array
    (
        "url"  => "/flow/",         // Regular expression to check urls
        "file" => "modules/flow.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
		"auth_required" => false,
    ),
    "podpishi_map" => array
    (
        "url"  => "/podpishi_map/",         // Regular expression to check urls
        "file" => "modules/podpishi_map.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
		"auth_required" => false,
    ),

);