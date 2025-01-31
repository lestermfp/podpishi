<?php

class db_federal_regions extends ActiveRecord\Model {
    static $table_name = 'federal_regions';

    static function getListArray(){

        $list = [];

        $regions = db_federal_regions::find('all', [
            'order' => 'code ASC',
        ]);

        foreach ($regions as $region){

            $list[] = $region->full_name;

        }

        return $list ;

    }

}

?>