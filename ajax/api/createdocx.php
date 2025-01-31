<?php
//require_once 'bootstrap.php';
require_once('../../config.php');
require_once('./phpword/vendor/autoload.php');

$fileName = "/var/www/podpishi.org/ajax/api/petition_template.docx";
$phpWord = \PhpOffice\PhpWord\IOFactory::load($fileName);

$sections = $phpWord->getSections();
$section = $sections[0]; // le document ne contient qu'une section
$arrays = $section->getElements();

// Adding an empty Section to the document...
$section = $phpWord->addSection();
// Adding Text element to the Section having font styled by default...
$section->addText(
    htmlspecialchars(
        '"Learn from yesterday, live for today, hope for tomorrow. '
            . 'The important thing is not to stop questioning." '
            . '(Albert Einstein)'
    )
);

//print '<pre>' . print_r($arrays, true) . '</pre>';
//exit();

$source = './user_sign.jpg';
$section->addImage($source);

// Saving the document as OOXML file...
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save('helloWorld.docx');

// Saving the document as HTML file...
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
$objWriter->save('helloWorld.html');


?>