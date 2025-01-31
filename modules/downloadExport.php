<?php

if (!is_authed()){
    print 'You have to login before download';
    exit();
}

/*
 * Used for one time download
 */
//print $_GET['parts'][2];

$filename = @basename($_GET['parts'][2]);

if ($filename == ''){
    print 'Nothing to download';
    exit();
}

$extension = explode('.', $filename);

if (!isset($extension[1]) OR count($extension) != 2 OR $extension[1] != 'xlsx'){
    print 'Wrong file to download';
    exit();
}

$final_path = $_CFG['root'] . 'cache/xlsx/' . $filename;

//print $final_path;

// clear cache
$podpishiXslxFactory = new podpishiXslxFactory();

if (!file_exists($final_path)){
    print 'File not found. Maybe you have to regenerate it';
    exit();
}

gdHandlerSkeleton::downloadFile($final_path, $filename);


?>