<?php


class db_pathways_2018ga extends ActiveRecord\Model {
    static $table_name = 'pathways';
    static $db = 'prime2018_copied';
}

class db_bycsv_gkh_2018ga extends ActiveRecord\Model {
    static $table_name = 'bycsv_gkh';
    static $db = 'prime2018_copied';
}

class db_target_ship_sectors_2018ga extends ActiveRecord\Model {
    static $table_name = 'target_ship_sectors';
    static $db = 'prime2018_copied';
}

class db_cikrf_results extends ActiveRecord\Model {
    static $table_name = 'cikrf_results';
}

class db_uik_results extends ActiveRecord\Model {
    static $table_name = 'uik_results';
}

class db_eparty_people extends ActiveRecord\Model {
    static $table_name = 'eparty_people';
}



class db_cik_uik extends ActiveRecord\Model {
    static $table_name = 'cik_uik';
}

class db_cik_people extends ActiveRecord\Model {
    static $table_name = 'cik_people';
}

class db_yd_logs extends ActiveRecord\Model {
    static $table_name = 'yd_logs';
}

class db_regions_list extends ActiveRecord\Model {
    static $table_name = 'regions_list';
}

class db_authors_list extends ActiveRecord\Model {
    static $table_name = 'authors_list';

    static $has_many = [
        [
            'authors_association',
            'class_name' => 'db_authors_to_campaigns',
            'foreign_key' => 'author_id',
            'order' => 'date DESC',
        ],
    ];

    static function getListForUser(db_main_users $user){

    	$domains = $user->getDomains();
    	if (empty($domains)) {
		    $domains = [-1];
	    }

        $conditions = ['domain IN (?)', $domains];

        if ($user->role == 'admin')
            $conditions = ['1=1'];

        $authors = db_authors_list::find('all', [
            'conditions' => $conditions,
            'order' => 'id DESC',
        ]);

        return $authors;

    }

    public function getCreatedBy(){

        $user = db_authors_list::find('first', [
            'conditions' => ['id=?', $this->created_by_id],
        ]);

        return $user ;

    }

    public function getCreatedByInfo(){

        $item = [];

        $created_by = $this->getCreatedBy();

        if (!empty($created_by))
            $item = [
                'id' => $created_by->id,
                'surname' => $created_by->surname,
                'name' => $created_by->name,
            ];

        return $item ;
    }

    public function getPublicInfo(){

        $item = $this->to_array();
        $item['phone_formatted'] = formatPhone($item['phone']);

        if ($this->socials_raw != '' AND $this->socials_raw != '[]')
            $item['socials'] = json_decode($this->socials_raw, true);

        if (!isset($item['socials']) OR !is_array($item['socials']))
            $item['socials'] = [];


        return $item ;

    }

    public function isEditableBy(db_main_users $user){

        if ($user->role == 'admin')
            return true;

        if ($this->created_by_id == $user->id)
            return true;

        if (!empty($user->getDomains()))
            if (in_array($this->domain, $user->getDomains()))
                return true;

        return false;

    }
}

class db_appeals_list extends ActiveRecord\Model {
    static $table_name = 'appeals_list';

    public function getAddressCity(){

        $address = trim($this->city) ;

        $array_parts = [];

        if ($this->region_name != '')
            $array_parts[] = $this->region_name;

        if ($this->rn_name != '')
            $array_parts[] = $this->rn_name;

        $address = trim($this->city);

        if (!empty($array_parts))
            $address .= ' (' . implode(", ", $array_parts) . ')';

        return $address;
    }

    public function getCampaign(){

        $campaign = db_campaigns_list::find_by_id($this->destination);

        return $campaign;
    }

    public function after_create(){

        $campaign = $this->getCampaign();

        if (!empty($campaign))
            $campaign->flushCacheKeys();

    }
}

class db_old_appeals extends ActiveRecord\Model {
    static $table_name = 'old_appeals';
}

class db_attaches_list extends ActiveRecord\Model {
    static $table_name = 'attaches_list';

    private $mimeToName = array(
        'image/jpeg' => '.jpg',
        'image/gif' => '.gif',
        'image/png' => '.png',
        'application/pdf' => '.pdf',
    );

    public function getLink() {
        $link = substr($this->read_attribute('download_hash_md5'), 0, 10) . '/' . $this->read_attribute('download_hash_md5') . $this->mimeToName[$this->read_attribute('mime')];

        return '/static/attach/' . $link;
    }
}


class db_mailQueue extends ActiveRecord\Model {
    static $table_name = 'mailQueue';
}

class db_main_activityLog extends ActiveRecord\Model {
    static $table_name = 'main_activityLog';
}


class db_main_users extends ActiveRecord\Model {
    static $table_name = 'main_users';

    public function getFullName()
    {
        return $this->surname.' '.$this->name;
    }

    public function getAvatar()
    {
        return '/static/attach/' . $this->avatar;
    }


    public function getDomains(){

        $domains = [];

        if ($this->domains != '' AND $this->domains != '[]')
            $domains = @json_decode($this->domains, true);

        if (!is_array($domains))
            $domains = [];

        return $domains;
    }



}

class db_meta_tags extends ActiveRecord\Model {
    static $table_name = 'meta_tags';
}


class db_authLogs extends ActiveRecord\Model {
    static $table_name = 'authLogs';
}



?>
