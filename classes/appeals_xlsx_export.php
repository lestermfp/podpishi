<?php

class podpishiXslxFactory {

    private $campaign = null ;
    private $basedir = '';

    public function __construct(){
        Global $_CFG ;

        $this->basedir = $_CFG['root'] . 'cache/xlsx/';

        if (!file_exists($this->basedir))
            mkdir($this->basedir);

        $this->clearPreviousCache();

    }

    private function clearPreviousCache(){

        $scandired = scandir($this->basedir);

        foreach ($scandired as $file)
            if (filemtime($this->basedir . $file) < time() - 3600)
                if ($file != '.' AND $file != '..')
                    unlink($this->basedir . $file);


    }

    public function setCampaign($campaign) {

        $this->campaign = $campaign;

    }

    public function getAppeals(){

        $appeals = [];

        if ($this->campaign->domain == 'yabloko')
            $appeals = db_appeals_list::find('all', [
                'select' => 'full_name, city, street_name, house_number, flat, phone, email, date, region_name, rn_name',
                'conditions' => ['destination=?', $this->campaign->id]
            ]);

        return $appeals ;
    }

    public function generateFilename(){

        $target_filename = gdHandlerSkeleton::str2url($this->campaign->title) . '_' . date('Y_m_d_h_i') . $this->campaign->id . '.xlsx';

        return $target_filename ;

    }

    public function downloadAs($filename = ''){

        if ($filename == '')
            $filename = $this->generateFilename();

        $xlsx = SimpleXLSXGen::fromArray( $this->getStruct());
        $xlsx->downloadAs($filename); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx


    }

    public function saveAs($path = ''){

        $xlsx = SimpleXLSXGen::fromArray( $this->getStruct());
        $result = $xlsx->saveAs($path); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx

        return $result;

    }

    public function getStruct(){

        $xlsx_keys = ["Реквизит: Название","Фамилия","Имя","Отчество","Дата сбора","УИК","Мобильный телефон","E-mail для рассылок","Реквизит (Россия): Адрес - тип","Реквизит (Россия): Адрес - страна","Реквизит (Россия): Адрес - регион","Реквизит (Россия): Адрес - район","Реквизит (Россия): Адрес - населенный пункт","Реквизит (Россия): Адрес - улица, номер дома","Улица","Дом","Реквизит (Россия): Адрес - квартира, офис, комната, этаж","Квартира","Наказ","Сборщик","Реакция","Кампания (тег)","Район","Комментарий"];

        $xlsx_struct = [
            $xlsx_keys,
        ];

        $appeals = $this->getAppeals();

        foreach ($appeals as $appeal){

            $item = [];

            foreach ($xlsx_keys as $key)
                $item[$key] = '';

            $parts_fio = explode(" ", $appeal->full_name, 3);

            if (isset($parts_fio[0]))
                $item['Фамилия'] = $parts_fio[0];

            if (isset($parts_fio[1]))
                $item['Имя'] = $parts_fio[1];

            if (isset($parts_fio[2]))
                $item['Отчество'] = $parts_fio[2];

            $item['Реквизит: Название'] = 'Физ. лицо';
            $item['Реквизит (Россия): Адрес - тип'] = 'Адрес доставки';
            $item['Реквизит (Россия): Адрес - страна'] = 'Россия';
            $item['Реквизит (Россия): Адрес - регион'] = $appeal->region_name;
            $item['Реквизит (Россия): Адрес - район'] = $appeal->rn_name;
            $item['Реквизит (Россия): Адрес - населенный пункт'] = $appeal->city;
            $item['Реквизит (Россия): Адрес - квартира, офис, комната, этаж'] = $appeal->flat;

            if (trim($appeal->street_name) != '')
                $item['Реквизит (Россия): Адрес - улица, номер дома'] = 'ул. ' . trim($appeal->street_name) . ', дом ' . $appeal->house_number;

            $phone = $appeal->phone;

            if ($this->campaign->domain == 'yabloko')
                if ($phone[0] == 7)
                    $phone[0] = 8;

            $item['Мобильный телефон'] = $phone;
            $item['E-mail для рассылок'] = $appeal->email;

            $item['Улица'] = trim($appeal->street_name);
            $item['Дом'] = $appeal->house_number;
            $item['Квартира'] = $appeal->flat;

            $item['Район'] = str_replace(' ', '_', $appeal->rn_name);


            $item['Дата сбора'] = $appeal->date->format('m/d/Y');

            $xlsx_struct[] = $item;

        }

        return $xlsx_struct;

    }

}

?>