<?php

$campaignItem = db_campaigns_list::find_by_id($_GET['parts'][2], [
    'select' => 'id, title, owner_id, whom, domain, petition_city',
]);


$campaign = new pdPetitions($campaignItem);

if (!$campaign->hasPrevi())
    redirect('/panel');


$page = 0;

if (isset($_GET['page']) AND is_numeric($_GET['page']) AND $_GET['page'] > 0)
    $page = $_GET['page'] - 1;

$isReesterMode = false ;

if (isset($_GET['listonly']))
    $isReesterMode = true ;

$appeals = db_appeals_list::find('all', [
    'conditions' => ['1=1 AND destination=?', $campaignItem->id],
    'order' => 'id ASC',
    'offset' => $page * 500,
    'limit' => 500,
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
            'Муниципальный район' => $_appeal->rn_name,
            'Телефон' => formatPhone($_appeal->phone),
            'Email' => ($_appeal->email),
        ];

        $number++;

        $list[] = $item ;

    }

    print gdHandlerSkeleton::generateSimpleHtmlTable($list);

    exit();

}
else {

    if ($campaignItem->id == 29){
        print '<style> body {line-height: 15px;} </style>';
    }

    foreach ($appeals as $_appeal){

        $address = trim($_appeal->city) . ', ул. ' . trim($_appeal->street_name) . ' ' . trim($_appeal->house_number) ;

        if ($_appeal->flat != '')
            $address .= ', кв. ' . trim($_appeal->flat) ;

        $content = $_appeal->appeal_text;

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

        if ($_appeal->appeal_title != '')
            $appeal_title = $_appeal->appeal_title ;


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

            <?php

            print nl2br($campaignItem->whom);
            ?>
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

            <?php

            if ($_appeal->role == 'worker'){
                ?>
                <p>Подтверждением моей связи с районом является место работы, расположенное в Пресненском районе: <?=$_appeal->job_name?>.</p>
                <?php
            }

            ?>

            <?php

            if ($_appeal->role == 'owner'){
                ?>
                <p><?=$_appeal->reason_name?>.</p>
                <?php
            }

            ?>

        </div>

        <div class="zayava-sign">
            <?=$_appeal->date->format('d.m.Y')?>
            <img src="<?=$_appeal->signature;?>" width="150">
        </div>

        <div class="pagebreak">

        </div>
        <?php


        //break ;
    }

}



?>


