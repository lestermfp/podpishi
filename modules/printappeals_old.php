<?php

$petitions = db_old_appeals::find('first', [
    'conditions' => ['reg_date>="2020-07-01 00:00:00"'],
]);

//print count($petitions);

//print '<pre>' . print_r($petitions, true) . '</pre>';

//exit();


$page = 0;

if (isset($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0)
    $page = $_GET['page'] - 1;

$isReesterMode = false ;

if (isset($_GET['listonly']))
    $isReesterMode = true ;

$appeals = db_old_appeals::find('all', [
    'conditions' => ['reg_date>="2020-07-01 00:00:00" AND destination="meriya"'],
    'select' => '*, appartment_raw as flat, name as full_name',
    'order' => 'id ASC',
    'offset' => $page * 1000,
    'limit' => 1000,
]);

?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php

if ($isReesterMode){

    $list = [];

    $previous_pages = 500 * $page ;

    $number = 1;
    foreach ($appeals as $_key => $_appeal){

        $address = trim($_appeal->city) . ', ул. ' . trim($_appeal->street_name) . ' ' . trim($_appeal->house_number) ;

        if ($_appeal->flat != '')
            $address .= ', кв. ' . trim($_appeal->flat) ;

        //$number = $number ;

        $item = [
            '#' => $number + $previous_pages,
            'ФИО' => $_appeal->full_name,
            'Адрес' => $address,
            'Район' => $_appeal->region_name,
            'Телефон' => formatPhone($_appeal->phone),
            'destination' => $_appeal->destination,
        ];

        $number++;

        $list[] = $item ;

    }

    print gdHandlerSkeleton::generateSimpleHtmlTable($list);

    exit();

}
else {

    //if ($campaignItem->id == 29){
    //    print '<style> body {line-height: 15px;} </style>';
    //}

    foreach ($appeals as $_appeal){

        $address = trim($_appeal->city) . ', ул. ' . trim($_appeal->street_name) . ' ' . trim($_appeal->house_number) ;

        if ($_appeal->flat != '')
            $address .= ', кв. ' . trim($_appeal->flat) ;

        $content = $_appeal->petition_text;

        $content = html_entity_decode($content);
        $content = strip_tags($content);

        $content = trim($content);

        $content = str_replace('  ', ' ', $content);
        $content = str_replace('  ', ' ', $content);
        $content = str_replace('   ', ' ', $content);
        $content = str_replace('  ', ' ', $content);

        $content = str_replace("\n\n", "\n", $content);
        $content = str_replace("\n", "</p><p>", $content);

        $content = str_replace("</p><p>  ", "</p><p>", $content);
        $content = str_replace("</p><p></p><p>", "</p><p>", $content);


        $content = ltrim($content, '</p><p>');

        $content = '<p>' . $content;


        $appeal_title = 'Уважаемый Сергей Семёнович!';


        ?>

        <style>

            body {
                font-family: "Times New Roman";
                font-size: 14px;
            }
            .header-right {
                text-align: right;
                line-height: 20px;
            }

            .zayava-title {
                text-align: center;
                margin-bottom: 10px;
            }

            .pagebreak {
                page-break-after: always;
            }

            .zayava-sign {
                text-align: right;
                position: relative;
            }

            .zayava-sign img {
                position: absolute;
                right: 35px;
                top: 35px;
            }

            .zayava-content {

            }

        </style>

        <br/>
        <div class="header-right">

            Мэру Москвы
            <br/>С. С. Собянину
            <br/><br/>


            от <?=$_appeal->full_name?><br/>
            проживающего по адресу:<br/>
            <?=$address?><br/>
            <?=formatPhone($_appeal->phone)?><br/>
        </div>

        <br/>
        <div class="zayava-title">
            <b><?=nl2br($appeal_title)?></b>
        </div>

        <div class="zayava-content">

            <?=$content;?>


        </div>

        <div class="zayava-sign">
            <?=$_appeal->reg_date->format('d.m.Y')?>
            <img src="<?=$_appeal->sign;?>" width="150">
        </div>

        <div class="pagebreak">

        </div>
        <?php


        //break ;
    }

}



?>


