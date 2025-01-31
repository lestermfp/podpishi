<?php

$_CFG['headers'] = array(
	'1' => array(
		'header' => 'modules/site_header2.php',
		'footer' => 'modules/site_footer.php',
	),
    '2' => array(
        'header' => 'modules/headers/panel_header.php',
        'footer' => 'modules/headers/panel_footer.php',
    ),
    'yabloko' => array(
        'header' => 'modules/headers/site_header_yabloko.php',
        'footer' => 'modules/headers/site_footer_yabloko.php',
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

    "1indexyabloko" => array
    (
        "url"  => "/1indexyabloko/",         // Regular expression to check urls
        "file" => "modules/index_yabloko.php",             // Main module file
        "req_headers" => "yabloko",                // Flag to include site footer and header
        "auth_required" => false,
        "domain" => ["podpishi60.ru"],
        "defaultpage" => true,
    ),


    "index" => array
    (
        "url"  => "/index/",         // Regular expression to check urls
        "file" => "modules/index_petitions.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
        "domain" => ["podpishi.org"],
        //"defaultpage" => true,
    ),

    "save-belarus" => array
    (
        "url"  => "/save-belarus/",         // Regular expression to check urls
        "file" => "modules/save_belarus_page.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
        "domain" => ["save-belarus.org"],
        "defaultpage" => true,
    ),


    "stos87y" => array
    (
        "url"  => "/stos87y/",         // Regular expression to check urls
        "file" => "modules/index_petitions.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
    ),

    "staging" => array
    (
        "url"  => "/staging/",         // Regular expression to check urls
        "file" => "modules/index_page_staging.php",             // Main module file
        "req_headers" => "0",                // Flag to include site footer and header
		"auth_required" => false,
    ),

    "apple_writes_ng" => array
    (
        "url"  => "/apple_writes_ng/",         // Regular expression to check urls
        "file" => "modules/apple_writes_ng.php",             // Main module file
        "req_headers" => "0",                // Flag to include site footer and header
        "auth_required" => false,
    ),


    "logout" => array
    (
        "url"  => "/logout/",         // Regular expression to check urls
        "file" => "modules/logout.php",             // Main module file
        "req_headers" => "0",                // Flag to include site footer and header
        "auth_required" => false,
    ),
    "flow" => array
    (
        "url"  => "/flow/",         // Regular expression to check urls
        "file" => "modules/flow.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
    ),
    "downloadExport" => array
    (
        "url"  => "/downloadExport/",         // Regular expression to check urls
        "file" => "modules/downloadExport.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
    ),
    "autopetition" => array
    (
        "url"  => "/autopetition/",         // Regular expression to check urls
        "file" => "modules/autopetition.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
    ),
    "autopetition_yabloko" => array
    (
        "url"  => "/autopetition_yabloko/",         // Regular expression to check urls
        "file" => "modules/autopetition_yabloko.php",             // Main module file
        "req_headers" => "none",                // Flag to include site footer and header
        "auth_required" => false,
    ),

    "trolley" => array
    (
        "url"  => "/\/trolley/",         // Regular expression to check urls
        "file" => "modules/podpishi_v2.php",             // Main module file
        "req_headers" => "0",                // Flag to include site footer and header
        "auth_required" => false,
    ),

    "auth_external" => array
    (
        "url"  => "/auth_external/",         // Regular expression to check urls
        "file" => "modules/auth_external.php",             // Main module file
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
    "cabinet" => array
    (
        "url"  => "/cabinet/",         // Regular expression to check urls
        "file" => "modules/cabinet_proxy.php",             // Main module file
        "req_headers" => "2",                // Flag to include site footer and header
        "sub_modules" => ['inner_header'],
        "auth_required" => true,
    ),
    "login" => array
    (
        "url"  => "/login/",         // Regular expression to check urls
        "file" => "modules/auth2.php",             // Main module file
        "req_headers" => "2",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => false,
    ),
    "no_auth" => array
    (
        "url"  => "/no_auth/",         // Regular expression to check urls
        "file" => "modules/auth2.php",             // Main module file
        "req_headers" => "2",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => false,
    ),
    "policy" => array
    (
        "url"  => "/policy/",         // Regular expression to check urls
        "file" => "modules/policy.php",             // Main module file
        "req_headers" => "-1",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => false,
    ),
    "automated_oferta" => array
    (
        "url"  => "/policy/",         // Regular expression to check urls
        "file" => "modules/automated_oferta.php",             // Main module file
        "req_headers" => "-1",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => false,
    ),

    "printappeals_yabloko" => array
    (
        "url"  => "/printappeals_yabloko/",         // Regular expression to check urls
        "file" => "modules/printappeals_yabloko.php",             // Main module file
        "req_headers" => "-1",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => true,
    ),

    "printappeals" => array
    (
        "url"  => "/printappeals/",         // Regular expression to check urls
        "file" => "modules/printappeals.php",             // Main module file
        "req_headers" => "-1",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => true,
    ),


    "oldprinter" => array
    (
        "url"  => "/oldprinter/",         // Regular expression to check urls
        "file" => "modules/printappeals_old.php",             // Main module file
        "req_headers" => "-1",                // Flag to include site footer and header
        "sub_modules" => [],
        "auth_required" => true,
        "domain" => ["podpishi.org"],
    ),

    "bicycle" => array
    (
        "url"  => "/bicycle/",         // Regular expression to check urls
        "file" => "modules/podpishi_v2.php",             // Main module file
        "req_headers" => "0",                // Flag to include site footer and header
        "auth_required" => false,
        "domain" => ["podpishi.org"],
    ),



);