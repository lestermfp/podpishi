<?php

include('config.php');
//require($_CFG['root'] . 'ajax/ajax_siteBlocks.php');


/*
	Try to parse url of destination page
*/

$_ADDR['raw'] = $_SERVER['REQUEST_URI'] ;
$_ADDR['url'] = $_SERVER['REQUEST_URI'] ;

if (substr($_ADDR['url'], -1, 1) == '/') $_ADDR['url'] = substr($_ADDR['url'], 0, -1);

if (strpos($_ADDR['url'], '/?') === false AND strpos($_ADDR['url'], '?') !== false)
    $_ADDR['url'] = substr_replace($_ADDR['url'], '/?', strpos($_ADDR['url'], '?'), 1);

if ($_ADDR['url'] == '/' OR empty($_ADDR['url']) OR substr($_ADDR['url'], 0, 2) == '/?') $_ADDR['url'] = '/index';

$_GET['parts'] = explode('/', $_ADDR['url']);

/*
	Searching for module to load by url
*/

$site_domain = $_SERVER['HTTP_HOST'];

$module_name = '';
$defaulepage_module_name = '';
foreach ($_CFG['modules'] as $name => $data){

    if (isset($data['domain'])) {

        if (!is_array($data['domain']))
            $data['domain'] = [$data['domain']];

        if (!in_array($site_domain, $data['domain']))
            continue;

    }

    if (isset($data['defaultpage']))
        $defaulepage_module_name = $name ;

    $urls = $data["url"];

    if (!is_array($data["url"]))
        $urls = [$data["url"]] ;

    foreach ($urls as $module_url)
        if(preg_match($module_url, $_ADDR['url'])){
            $module_name = $name ;
            break 2;
        }
}

// Load module 404 if another module not found
if (empty($module_name)){

    $module_name = '404';

    // setup default page
    if ($defaulepage_module_name != '')
        $module_name = $defaulepage_module_name;

    // if petition found?

    if (isset($_GET['parts']['1']) AND !is_array($_GET['parts']['1']) AND !empty($_GET['parts']['1'])){

        $possible_petiton = $_GET['parts']['1'];

        if ($possible_petiton == 'sofia_sepega')
            $possible_petiton = 'sofia_sapega';

        $petitionInfo = db_campaigns_list::find('first', [
            'conditions' => ['LOWER(url)=LOWER(?) OR (url_readonly!="" AND LOWER(url_readonly)=LOWER(?))', $possible_petiton, $possible_petiton],
        ]);

        if (!empty($petitionInfo)) {
            $module_name = 'autopetition';

            if ($petitionInfo->domain == 'yabloko')
                $module_name = 'autopetition_yabloko';

            if ($petitionInfo->domain == 'podpishi')
                if (isset($_GET['parts']['2']) AND $_GET['parts']['2'] == 'offer')
                    $module_name = 'automated_oferta';

        }
    }


    //if ($isExistsPetition)

}

// Load module that display authorization error if page can't be displayed to non authed user
if ($_CFG['modules'][$module_name]['auth_required'] == true AND !is_authed())
    $module_name = 'no_auth';

if ($_SERVER['HTTP_HOST'] == 'call.ru')
    $module_name = 'call';


$desired_meta = $module_name;
if (isset($_CFG['modules'][$module_name]['use_meta']))
    $desired_meta = $_CFG['modules'][$module_name]['use_meta'] ;

$_CFG['meta'][$module_name] = gdPanel::getMetaFor($desired_meta);

if (in_array($_SERVER['HTTP_HOST'], [ 'nikitskaya.fondvnimanie.ru'])) {


    if (!in_array($module_name, array('nikitskaya'))) {

        $module_name = 'nikitskaya';

    }

    $_CFG['meta']['indexv5']['property="og:url"'] = 'https://' . $_SERVER['HTTP_HOST'] . '/';
    $_CFG['meta']['indexv5']['name="twitter:domain"'] = 'https://' . $_SERVER['HTTP_HOST'] . '/';

}

if (isset($_GET['module_name']))
    print $module_name ;

$_module = $_CFG['modules'][$module_name] ;

$_CFG['page_title'] = '';
$_CFG['page_description'] = '';


if (isset($_module['req_headers']) AND isset($_CFG['headers'][$_module['req_headers']])){

    if ($module_name == 404)
        http_response_code(404);

    include($_CFG['root'] . $_CFG['headers'][$_module['req_headers']]['header']);

    if (isset($_module['sub_modules']))
        foreach ($_module['sub_modules'] as $_subname)
            include($_CFG['root'] . 'modules/headers/' . $_subname . '.php');

    include($_CFG['root'] . $_CFG['modules'][$module_name]['file']);
    include($_CFG['root'] . $_CFG['headers'][$_module['req_headers']]['footer']);
}
else {
    include($_CFG['root'] . $_CFG['modules'][$module_name]['file']);
}

if (isset($_GET['showsessfb'])) {
    //print '<pre>' . print_r($_SESSION, true) . '</pre>';
    //print '<pre>' . print_r($_CFG, true) . '</pre>';
}
//print '<pre>' . print_r($page, true) . '</pre>';
?>
