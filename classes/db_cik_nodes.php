<?php

class db_cik_nodes extends ActiveRecord\Model {
    static $table_name = 'cik_nodes';

    public function buildTree($max_levelid = 10){

        $initialTree = [2 => '','','','','','','','','','', 'flats_n' => '', 'flats' => '', 'uik_num' => '', 'id' => ''];

        $output = $this->lookupTillLevelid($initialTree, $max_levelid);

        return $output;

    }

    public function lookupTillLevelid($currentItem, $max_levelid = 8){

        $currentItem[$this->levelid] = $this->text ;
        $currentItem['uik_num'] = $this->uik_num ;


        if ($this->isHouse()){


            $children = $this->getChildren();

            $currentItem['flats_n'] = count($children) ;
            $currentItem['flats'] = [];

            if ($currentItem['flats_n'] == 0)
                $currentItem['flats_n'] = 1;


            foreach ($children as $child)
                $currentItem['flats'][] = $child->text ;

            $currentItem['flats'] = implode("; ", $currentItem['flats']);

            $currentItem['id'] = $this->id ;

            //return [$currentItem];
        }



        //$tmpItem = $currentItem;

        $output = [];

        $children = $this->getChildren();

        if ($this->parent == 338832305){

            //print 'Parent Pogranichnaya 9 lv' . $this->levelid . ' > ' . $this->text . '<br/>';

        }

        if ($this->levelid >= 8){

            return [$currentItem];

        }

        if (empty($children)){

            //print 'No children at ' . $this->text . '<br/>';

            return [$currentItem];

        }



        //print '<pre>' . print_r($children, true) . '</pre>';

        //exit();

        foreach ($children as $child){

            $tmpItem = $currentItem;

            $tmpItem[$child->levelid] = $child->text ;

            if ($child->levelid < $max_levelid){

                if ($this->id == 338823432){

                    print 'Pogranichnaya ' . $child->text . '<br/>';

                    print 'Going into (lv: ' . $child->levelid . ') ' . $child->text . ' (from ' . $this->text . ')' . '<br/>';

                    //continue ;
                }

                //print 'Going into ' . $child->text . ' (from ' . $this->text . ')' . '<br/>';

                if ($this->id == 338823432){

                }
                else {




                }


                if ($this->id == 338823432){

                    //print '<pre>' . print_r($subItems, true) . '</pre>';

                }

                $subItems = $child->lookupTillLevelid($currentItem, $max_levelid);

                foreach ($subItems as $subItem)
                    $output[] = $subItem ;









            }
            else {

                $output[] = $tmpItem ;

                //$output[] = $item;

            }


        }

        return $output ;

    }

    public function getChildren(){

        $raw_json = json_decode($this->raw_json, true);

        if (!is_array($raw_json)){

            $nodes = db_cik_nodes::find('all', [
                'conditions' => ['parent=?', $this->id],
            ]);

            return $nodes ;

        }

        $ids = [];

        foreach ($raw_json as $item)
            $ids[] = $item['id'];

        if (empty($ids))
            return [];

        $nodes = db_cik_nodes::find('all', [
            'conditions' => ['id IN (?)', $ids],
        ]);

        return $nodes ;

    }

    public function isHouse(){

        return ($this->levelid == 8);

    }


}

?>