<?php

//print '<pre>' . print_r($_SESSION, true) . '</pre>';

class uniGeocoder {

    static function requestQualifiedCoordsByAddress($address, $apikey = '', $forced_geo_system = '') {

        $address = str_replace(' ул ', ' улица ', $address);
        $address = str_replace('ул.', 'улица', $address);

        if (strpos($address, 'кв.') !== false){

            $address = explode('кв.', $address);
            $address = $address[0] ;

        }

        if (strpos($address, 'квартира') !== false){

            $address = explode('квартира', $address);
            $address = $address[0] ;

        }

        $output = [
            'coords' => [
                'lat' => '',
                'lng' => '',
            ],
            'building' => [],
            'used_engine' => '',
            'result' => false,
        ];

        if ($forced_geo_system == '' OR $forced_geo_system == 'dadata') {

            $dadata = new dadata();

            $fields = ['query' => $address, 'count' => 3];
            $result = $dadata->suggest('address', $fields);

            if (isset($result['suggestions']) && isset($result['suggestions'][0])) {
                $building = $result['suggestions'][0];
                $buildingData = $building['data'];

                $output['dadataData'] = $buildingData;

                if (((int)$buildingData['qc_geo'] == 0 AND $buildingData['geo_lat'] != '') OR $forced_geo_system != '') {
                    $output['coords']['lat'] = $buildingData['geo_lat'];
                    $output['coords']['lng'] = $buildingData['geo_lon'];
                    $output['used_engine'] = 'dadata';
                    $output['result'] = true;
                    $output['dadata_responce'] = $result;

                    $output['building'] = self::extractBuildingInfo_dadata($buildingData);

                    return $output;
                }
            }

        }

        if ($forced_geo_system == '' OR $forced_geo_system == 'yd')
            if (!$output['result']) {


                $urlUsed = 'https://spb2019.yabloko.ru/static/parsers/lo_buildings/coords_parser_public.php?address=' . urlencode($address);

                if ($apikey != '')
                    $urlUsed .= '&apikey=' . $apikey ;

                $result = self::get_as_array($urlUsed);

                if (isset($result['coords'])) {
                    $output['coords']['lat'] = $result['coords'][1];
                    $output['coords']['lng'] = $result['coords'][0];
                    $output['used_engine'] = 'yd';
                    $output['result'] = (!empty($result['coords'][1]) && !empty($result['coords'][0]));
                    $output['yd_responce'] = $result;

                    $output['building'] = self::extractBuildingInfo_yd($result);

                }
            }


        return $output;

    }

    static function extractBuildingInfo_dadata($dadata_reply){

        $building_info = [
            'has_building' => false,
            'error_text' => '',
        ];

        if (!isset($dadata_reply['house'])){
            $building_info['error_text'] = 'Ntf info by address';
            return $building_info;
        }

        $GeocoderMetaData = $dadata_reply;

        //print '<pre>' . print_r($yd_reply, true) . '</pre>';
        //exit();
        //print '<pre>' . print_r($GeocoderMetaData, true) . '</pre>';
        //exit();

        $house_number = $GeocoderMetaData['house'];

        if ($GeocoderMetaData['block_type'] != '') {

            $delim = '';
            if (mb_strlen($GeocoderMetaData['block_type'], 'UTF-8') > 1)
                $delim = ' ';

            $house_number .= $delim . $GeocoderMetaData['block_type'] . $delim . $GeocoderMetaData['block'];
        }

        $street_name = $GeocoderMetaData['street_with_type'];
        if ($GeocoderMetaData['street_with_type'] == '')
            $street_name = $GeocoderMetaData['city_district_with_type'];


        $building_info = array_merge($building_info, array(
            'street_name' => $street_name,
            'house_number' => $house_number,
            'city' => $GeocoderMetaData['city'],
            'lng' => $GeocoderMetaData['geo_lon'],
            'lat' => $GeocoderMetaData['geo_lat'],
        ));

        $building_info['has_building'] = true;

        return $building_info;

    }

    static function extractBuildingInfo_yd($yd_reply){

        $building_info = [
            'has_building' => false,
            'error_text' => '',
        ];

        if (!isset($yd_reply['coords']['feature']['GeocoderMetaData'])){
            $building_info['error_text'] = 'Ntf info by address';
            return $building_info;
        }

        $GeocoderMetaData = $yd_reply['coords']['feature']['GeocoderMetaData'];

        //print '<pre>' . print_r($yd_reply, true) . '</pre>';
        //exit();
        //print '<pre>' . print_r($GeocoderMetaData, true) . '</pre>';
        //exit();

        $CountryNameCode = self::recursiveFind($GeocoderMetaData, 'CountryNameCode');

        if (empty($CountryNameCode) OR $CountryNameCode != "RU"){
            $building_info['error_text'] = 'Not in Russia';
            return $building_info;
        }

        $PremiseNumber = self::recursiveFind($GeocoderMetaData, 'PremiseNumber');
        $ThoroughfareName = self::recursiveFind($GeocoderMetaData, 'ThoroughfareName');
        $DependentLocalityName = self::recursiveFind($GeocoderMetaData, 'DependentLocalityName');
        $LocalityName = self::recursiveFind($GeocoderMetaData, 'LocalityName');
        $AdministrativeAreaName = self::recursiveFind($GeocoderMetaData, 'AdministrativeAreaName');

        //var_dump($AdministrativeAreaName);
        //exit();

        if (empty($ThoroughfareName) AND !empty($DependentLocalityName))
            $ThoroughfareName = $DependentLocalityName;

        if (empty($LocalityName) AND !empty($AdministrativeAreaName))
            $LocalityName = $AdministrativeAreaName;

        //var_dump($LocalityName);
        //print '<pre>' . print_r($GeocoderMetaData, true) . '</pre>';

        //exit();

        if (empty($PremiseNumber)){
            $building_info = 'Not found building number' ;
            return $building_info;
        }

        $point = $yd_reply['coords'] ;
        $lat = $point['1'];
        $lng = $point['0'];

        $building_info = array_merge($building_info, array(
            'street_name' => $ThoroughfareName,
            'house_number' => $PremiseNumber,
            'city' => $LocalityName,
            'lng' => $lng,
            'lat' => $lat,
        ));

        $building_info['has_building'] = true;

        return $building_info;

    }

    static public function recursiveFind(array $array, $needle)
    {
        $iterator  = new RecursiveArrayIterator($array);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
    }

    static function get_as_array($url) {
        $options = [
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER         => true, // return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING       => '',   // handle all encodings
            CURLOPT_AUTOREFERER    => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,  // timeout on connect
            CURLOPT_TIMEOUT        => 120,  // timeout on response
            CURLOPT_MAXREDIRS      => 5,    // stop after ... redirects
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $response = iconv('windows-1251','utf-8', $response);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // $header = substr($response, 0, $header_size);
        $content = substr($response, $header_size);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($content === false) return ('Error “'.$httpcode.'”: '.$url.' returns an error ('.curl_error($ch).').' . PHP_EOL);
        else if ($httpcode !== 200) return ('Error “'.$httpcode.'”: '.$url.' returns an error ('.curl_error($ch).').' . PHP_EOL);
        else {
            curl_close($ch);

            $json = json_decode($content, true, 512, 1048576);
            if (json_last_error() !== JSON_ERROR_NONE) return('JSON error “'.json_last_error_msg().'”: ' . $url.' replied with non-json content: '.PHP_EOL.$response.PHP_EOL);
            else {
                return $json;
            }
        }
    }

}

?>