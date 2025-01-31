<?php

include('../../config.php');

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);

class estasCalcer {

    static function calc__apiRegionName(){

        $buildings = db_bycsv_gkh_2018ga::find('all', [
            'conditions' => ['subregion_status_spb2019="inqueue" AND pokego_entrances_amount>0'],
            'select' => 'id, lat, lng, subregion_id_spb2019, subregion_status_spb2019',
            'limit' => 3000,
            'order' => 'rand()',
        ]);

        $list = [];

        foreach ($buildings as $_building){

            $_building = db_bycsv_gkh_2018ga::find_by_id($_building->id, [
                'select' => 'id, lat, lng, subregion_id_spb2019, subregion_status_spb2019, spb2019_region_name',
            ]);

            if ($_building->subregion_status_spb2019 == 'done') {
                print 'Ignored' . "\r\n";
                continue;
            }

            $apiUrl = 'https://fempolitics.ru/ajax/ajax_utils.php?context=get__buildingSubregionByCoords&lat=' . $_building->lat . '&lng=' . $_building->lng;

            //print $apiUrl . '<br/>';

            $reply = json_decode(file_get_contents($apiUrl), true);

            //print '<pre>' . print_r($reply, true) . '</pre>';
            //exit();

            $_building->subregion_id_spb2019 = $reply['result']['subregion_id'];
            $_building->spb2019_region_name = $reply['result']['region_name'];
            $_building->subregion_status_spb2019 = 'done';

            $_building->save();

            $item = $_building->to_array();

            $list[] = $item ;

            print "Updated: " . $_building->id . ' ' . $reply['result']['subregion_id'] . ", " . $reply['result']['region_name'] . "\r\n";

            //break ;

        }

        //print gdHandlerSkeleton::generateSimpleHtmlTable($list);

        //redirect('http://podpishi.org/ajax/ajax_stand.php?context=calc__apiRegionName');

        exit();

    }


}

estasCalcer::calc__apiRegionName();

?>