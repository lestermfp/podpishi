<?php

$campaignItem = db_campaigns_list::find_by_id($_GET['parts'][2], [
    'select' => 'id, title, owner_id, whom, domain, petition_city, appeal_text, appeal_title, author_id, post_address, address_reply_full',
]);


$pdCampaign = new pdPetitions($campaignItem);

if (!$pdCampaign->hasPrevi())
    redirect('/panel');

if ($campaignItem->domain != 'yabloko')
    redirect('/panel');

$appeals = db_appeals_list::find('all', [
    'conditions' => ['1=1 AND destination=?', $campaignItem->id],
    'select' => 'id, full_name, city, rn_name, region_name, city',
    'order' => 'id ASC',
]);


$content = $campaignItem->appeal_text;

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


?>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<style>

    body {
        font-family: "Times New Roman";
        font-size: 14pt;
        padding: 1cm;
    }
    .header-right {
        text-align: right;
        line-height: 20px;
    }

    .campaign-title, .campaign-whom {
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

    .campaign-content {

    }

</style>

<br/>

<div class="campaign-whom"><b><?=$campaignItem->whom?></b></div>
<div class="campaign-address"><?=$campaignItem->post_address?></div>

<br/>
<br/>

<div class="campaign-title"><b><?=$campaignItem->appeal_title?></b></div>

<div class="campaign-content">

    <?=$content?>

</div>
<br/><br/>

<div class="campaign-author_pretitle"><b>Инициатор петиции «<?=$campaignItem->title?>»:</b></div>
<br/>
<div class="campaign-author">

    <b><?=$campaignItem->getAuthor()->surname?> <?=$campaignItem->getAuthor()->name?> <?=$campaignItem->getAuthor()->middlename?></b>, <?=mb_lcfirst($campaignItem->getAuthor()->description, 'UTF-8')?>, контакты: <?=$campaignItem->getAuthor()->phone?>, <a href="mailto:<?=$campaignItem->getAuthor()->email?>"><?=$campaignItem->getAuthor()->email?></a>
    <br/>
    Почтовый адрес инициатора петиции: <?=mb_lcfirst($campaignItem->address_reply_full, 'UTF-8')?>

</div>
<br/>
<br/>

<div class="campaign-signs_pretitle"><b>Петицию «<?=$campaignItem->title?>» поддержали:</b></div>
<br/>
<div class="campaign-signs">

    <?php

    $number = 1 ;
    foreach ($appeals as $_appeal){

        $full_name = $_appeal->full_name;

        $location = $_appeal->city ;

        if ($_appeal->rn_name != ''){
            $location .= ' (' . $_appeal->region_name . ', ' . $_appeal->rn_name . ')';
        }
        else {

            if ($_appeal->city != $_appeal->region_name)
                $location .= ' (' . $_appeal->region_name . ')';
        }

        if ($_appeal->region_name == 'не РФ'){

            $location = 'проживающий вне территории РФ (' . $_appeal->city . ')' ;

        }

        ?>

            <div><b><?=$number?>. <?=$full_name?>,</b> <?=$location?></div>

        <?php

        $number++ ;

    }

    ?>



</div>

<br/>


