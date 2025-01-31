<?php

if (!is_authed()){
    redirect('/');
    exit();
}


$_CFG['panelModules'] = array(
    'petitionedit' => array(
        'file' => 'panel_editpetition',
        'previ' => array('admin', 'settler', 'coordinator'),
    ),
    'petitionslist' => array(
        'file' => 'panel_petitionslist',
        'previ' => array('admin', 'settler', 'coordinator'),
    ),
    'appeals' => array(
        'file' => 'panel_appeals',
        'previ' => array('admin', 'settler', 'coordinator'),
    ),
    'accounts' => array(
        'file' => 'panel_accounts',
        'previ' => array('admin'),
    ),
    'authors' => array(
        'file' => 'panel_authors',
        'previ' => array('admin', 'coordinator'),
    ),
    'editauthor' => array(
        'file' => 'panel_editauthor',
        'previ' => array('admin', 'coordinator'),
    ),
    'editaccount' => array(
        'file' => 'panel_editaccount',
        'previ' => array('admin'),
    ),
    'welcome' => array(
        'file' => 'panel_welcome',
        'previ' => array('admin', 'guest', 'coordinator'),
    ),
    'statistics' => array(
        'file' => 'panel_statistics',
        'previ' => array('admin', 'guest', 'coordinator'),
    ),
);

$_CFG['panelModulesDefault'] = array(
    'settler' => 'petitionslist',
    'admin' => 'petitionslist',
    'coordinator' => 'petitionslist',
    'guest' => 'welcome',
);


$requestedModule = $_CFG['panelModules'][$_CFG['panelModulesDefault'][$_USER->getOptions('role')]];
$requestedModule['key'] = $_CFG['panelModulesDefault'][$_USER->getOptions('role')];

if (isset($_GET['parts'][2]) AND !empty($_GET['parts'][2]))
    if (isset($_CFG['panelModules'][$_GET['parts'][2]]) AND in_array($_USER->getOptions('role'), $_CFG['panelModules'][$_GET['parts'][2]]['previ'])){
        $requestedModule = $_CFG['panelModules'][$_GET['parts'][2]] ;
        $requestedModule['key'] = $_GET['parts'][2] ;
    }

$profileInfo = $_USER->getOptions('id, email, name, surname, role, extra_class, petition_city');


if(isset($requestedModule['noheader'])) {
    include_once($_CFG['root'] . 'modules/panel/' . $requestedModule['file'] . '.php');
    exit;
}



//$headerPath = $_CFG['root'] . 'modules/cabinet_header.php';

//if (isset($requestedModule['header']))
//    $headerPath = $_CFG['root'] . 'modules/' . $requestedModule['header'] . '.php' ;

//include_once ($headerPath);
include_once ($_CFG['root'] . 'modules/cabinet.php');


if (isset($_GET['standartOil'])){

    //print '<pre>' . print_r($_CFG['sqlLogs'], true) . '</pre>';

}
?>
