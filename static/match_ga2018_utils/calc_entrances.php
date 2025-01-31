<?php

include('../../config.php');

ini_set('display_errors', true);
ini_set('error_reporting',  E_ALL);
error_reporting(E_ALL + E_STRICT);

$pathwaysSpb = db_target_ship_sectors_2018ga::find('all', [
    'conditions' => ['sector_name LIKE ?', '%Петербург%'],
    'select' => 'id, sector_name',
]);

$pathwaysScopeIds = gdHandlerSkeleton::collectKeys($pathwaysSpb, ['id']);


$pathways = db_pathways_2018ga::find('all', [
    'conditions' => ['sector_id IN (?) AND final_info!="" AND spb2019_status="new"', $pathwaysScopeIds],
]);

//spb2019_status

print 'Count pathways: ' . count($pathways) . '<br/>';

foreach ($pathways as $_number => $_pathway){

    $final_info = json_decode($_pathway->final_info, true);

    print '<pre>' . print_r($final_info, true) . '</pre>';

    foreach ($final_info['visited_ids'] as $_visited_id => $_entrances){

        $building = db_bycsv_gkh_2018ga::find_by_id($_visited_id, [
            'select' => 'id, gkh_id, pokego_entrances_full, pokego_entrances_amount, pokego_flats, living_quarters_count, entrance_count',
        ]);

        if (empty($building))
            continue ;

        $this_pathway_entrances = json_decode($_entrances, true);

        // по умолчанию берем значения из текущего pathway
        //$_building_entrances = json_decode($_entrances, true);

        //$building->pokego_entrances_full = '5';


        // но если есть уже ранее взятый, то берём его
        if ($building->pokego_entrances_full == ''){

            $_building_entrances = $this_pathway_entrances;

        }
        else {

            // сверяем, дополняя пройденные подъезды из разных pathway
            $_building_entrances = json_decode($building->pokego_entrances_full, true);


            //$this_pathway_entrances = [0,0,1,1,1,1,1, 1];
            //$_building_entrances = [0,0,1,1,1,1,1, 1];


            foreach ($_building_entrances as $_key => $_entrance){

                if ($this_pathway_entrances[$_key] == 1)
                    $_building_entrances[$_key] = 1 ;

            }

        }



        $entrances_visited = 0;

        foreach ($_building_entrances as $_is_visited) {

            if ($_is_visited == 1)
                $entrances_visited++;
        }

        $flats_per_entrance = 0;

        if ($entrances_visited > 0)
            $flats_per_entrance = $building->living_quarters_count / $building->entrance_count ;

        $building->pokego_entrances_amount = $entrances_visited;
        $building->pokego_flats = $flats_per_entrance * $entrances_visited ;
        $building->pokego_entrances_full = json_encode($_building_entrances);
        $building->save();

        print 'Updated building ' . $building->id . '<br/>';
        print '<pre>' . print_r($_building_entrances, true) . '</pre>';
        print '<pre>' . print_r($building, true) . '</pre>';

        //exit();

    }

    foreach ($final_info['buildings_flats'] as $_building_id => $_info) {

        $building = db_bycsv_gkh_2018ga::find_by_id($_building_id, [
            'select' => 'id, gkh_id, pokego_entrances_full, pokego_entrances_amount, pokego_flats, living_quarters_count, entrance_count, visitplan_2018',
        ]);

        if (empty($building))
            continue;

        if ($building->visitplan_2018 == 0){
            $building->visitplan_2018 = 1;
            $building->save();

            print "VIsitplan set<br/>";
        }
    }


    $_pathway->spb2019_status = 'done';
    $_pathway->save();


    if ($_number > 200)
        break ;

    //print '<pre>' . print_r($final_info, true) . '</pre>';
    //print '<pre>' . print_r($_pathway, true) . '</pre>';

    //exit();

}

?>
