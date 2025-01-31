<?php

include('../../config.php');

class estasCalcer {

    static function outputBySubregions(){

        $buildingsRaw = db_bycsv_gkh_2018ga::find('all', [
            'conditions' => ['pokego_entrances_amount>0 AND subregion_status_spb2019="done" AND subregion_id_spb2019>0'],
            'select' => 'subregion_id_spb2019 as subregion_id, COUNT(*) as buildings_opened, SUM(pokego_entrances_amount) as total_entrances_opened, SUM(pokego_flats) as pokego_flats_opened',
            'group' => 'subregion_id',
        ]);

        $buildingsAllRaw = db_bycsv_gkh_2018ga::find('all', [
            'conditions' => ['visitplan_2018=1 AND subregion_status_spb2019="done" AND subregion_id_spb2019>0'],
            'select' => 'subregion_id_spb2019 as subregion_id, COUNT(*) as buildings_plan, SUM(entrance_count) as entrances_plan, SUM(living_quarters_count) as flats_plan',
            'group' => 'subregion_id',
        ]);



        $buildings = [];

        foreach ($buildingsRaw as $_building)
            $buildings[$_building->subregion_id] = $_building->to_array();

        foreach ($buildingsAllRaw as $_buildingAll){

            $buildings[$_buildingAll->subregion_id]['buildings_plan'] = $_buildingAll->buildings_plan ;
            $buildings[$_buildingAll->subregion_id]['entrances_plan'] = $_buildingAll->entrances_plan ;
            $buildings[$_buildingAll->subregion_id]['flats_plan'] = $_buildingAll->flats_plan ;

        }

        //print '<pre>' . print_r($buildings, true) . '</pre>';

        $output = [
            'bySubregions' => $buildings,
        ];

        print json_encode($output);

        exit();

        //print '<pre>' . print_r($buildings, true) . '</pre>';


        //exit();

    }


}

estasCalcer::outputBySubregions();

?>