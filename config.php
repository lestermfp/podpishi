<?php
//print '<pre>' . print_r($_SERVER, true) . '</pre>';

error_reporting(0);
$display_errors = true ;
if ( isset($display_errors) AND @$_SERVER['REMOTE_ADDR'] == '37.55.203.47'){

    ini_set('display_errors', true);
    ini_set('error_reporting',  E_ALL);
    error_reporting(E_ALL + E_STRICT);
}

if (isset($_GET['fefe'])){

    ini_set('display_errors', true);
    ini_set('error_reporting',  E_ALL);
    error_reporting(E_ALL + E_STRICT);

}


// Find out the root directory for site
$_CFG['root'] = dirname(realpath(__FILE__)) . '/' ;

if (!isset($no_session)){
// Set the sessions dir and start session
    $save_path = $_CFG['root'] . 'cache/petition';

    session_save_path($save_path);
    session_set_cookie_params(3600 * 24 * 31);
    session_start();
}

// Set default timezone to prevent excessive notices
date_default_timezone_set('Europe/Moscow');

// Time to load other config files
foreach(glob($_CFG['root'] . "config/*.php") as $file){
    require_once $file;
}

// Include useful functions collection
require_once($_CFG['root'] . 'functions.php');
require_once($_CFG['root'] . 'classes/tgBotApi.php');


if (!isset($no_db)){
    // Load activerecord ORM library
    require_once $_CFG['root'] . 'classes/activerecord/ActiveRecord.php';

    // Create DB connection
    $_cfg = ActiveRecord\Config::instance();

    // Load activerecord models in unusual way
    require_once($_CFG['root'] . 'classes/db_models.php');

    $_cfg->set_connections(array('development' =>'mysql://'. $_CFG['db']['username'] .':'. $_CFG['db']['password'] .'@'. $_CFG['db']['server'] .'/'. $_CFG['db']['database'] .''));
}

// Check the DB connection
try {
    Activerecord\Connection::instance() ;
} catch (Exception $e) {
    //if (isset($_GET['cfg'])) print '<pre>' . print_r($e, true) . '</pre>';

    print 'Exception captured. Contact the administration.';
    exit();
}


$_CFG['db'] = array(); // flush mysql credentials after connecting

// Set encoding
db_main_users::connection()->query('SET names utf8');


// Include site class
require_once($_CFG['root'] . 'classes/gamerhost_class.php');


function gd_my_autoload ($pClassName) {
    Global $_CFG;

    $classesAvailable = [

        'dadata' => 'dadata.php',
        'uniGeocoder' => 'uniGeocoder.php',
        'db_campaigns_list' => 'db_campaigns_list.php',
        'db_federal_regions' => 'db_federal_regions.php',
        'podpishiXslxFactory' => 'appeals_xlsx_export.php',
        'db_cik_nodes' => 'db_cik_nodes.php',

        'db_authors_to_campaigns' => 'db_authors_to_campaigns.php',


    ];

    if (!isset($classesAvailable[$pClassName])){
        print 'exited at AT/AT ' . $pClassName;
        exit();
    }

    include_once($_CFG['root'] . 'classes/' . $classesAvailable[$pClassName]);

    return true ;
}

spl_autoload_register("gd_my_autoload");

/*
	Set the HTTP_REFERER
*/
if (isset($_SERVER['HTTP_REFERER']) AND !empty($_SERVER['HTTP_REFERER']) AND !isset($_SESSION['origin']))
    $_SESSION['origin'] = $_SERVER['HTTP_REFERER'];

if (isset($_GET['utm_source']) AND isset($_GET['utm_medium']) AND isset($_GET['utm_campaign'])){

    $_SESSION['utmList'] = array(
        'utm_source' => $_GET['utm_source'],
        'utm_medium' => $_GET['utm_medium'],
        'utm_campaign' => $_GET['utm_campaign'],
    );

}

/*
	Load user's info if he authorized
*/

if (isset($_GET['clear_sess'])) $_SESSION = array();


if (is_authed('helper')){

    //if ($_SESSION['user']['id'] == 1)
    //$_SESSION['user']['id'] = 7;

    $_USER = new gh_user('db_main_users');
    $_USER->loadById($_SESSION['user']['id']);

    $last_activity = $_USER->getOptions('last_activity');

    if (!is_object($last_activity) OR time() - $last_activity->format('U') > 30) {
        $_USER->setOptions('last_activity', 'now');
        if ($_USER->getOptions('last_ip') != getRealIp())
            $_USER->setOptions('last_ip', getRealIp());


    }


    if ($_USER->getOptions('role') == 'declined' OR $_USER->getOptions('role') == 'none'){
        $_SESSION = array();
        redirect('/');
    }

    if ($_USER->getOptions('is_banned') == '1'){
        print 'Your account has banned';
        exit();
    }

    if ($_USER->getOptions('role') == 'coordinator_l')
        if ($_USER->getOptions('last_visit')->format('d.m.Y') != date('d.m.Y') OR date('H') >= 22){

            $_USER->genNewTmpPasswordForVolt();

            $_SESSION = array();
            redirect('/');
        }


}

if (GetRealIp() != '46.138.62.217'){
    //print '404';
    //exit();
}
//print '<pre>' . print_r($user, true) . '</pre>';
?>
