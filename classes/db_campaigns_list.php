<?php

class db_campaigns_list extends ActiveRecord\Model {
    static $table_name = 'campaigns_list';

    static $cache_keys = ['getAuthorsCached', 'appealsAmountAtId'];

    static $has_many = [
        [
            'authors_association',
            'class_name' => 'db_authors_to_campaigns',
            'foreign_key' => 'campaign_id',
            'order' => 'date DESC',
        ],
    ];

    public $url_opened_by = '';

    public function getCampaignId(){

        return $this->id ;

    }

    public function setUrlOpenedBy($url_opened_by){

        $this->url_opened_by = $url_opened_by;

    }

    public function getUrl(){

        if ($this->url_opened_by != '')
            if ($this->url_opened_by == $this->url_readonly)
                return $this->url_opened_by ;

        return $this->url ;

    }

    public function getAuthor(){

        $author = db_authors_list::find_by_id($this->author_id);

        return $author;

    }

    public function flushCacheKeys(){

        foreach (self::$cache_keys as $cache_key)
            gdCache::remove($cache_key . '::' . $this->getCampaignId());

    }

    public function getAuthors(){

        $authors_association = db_authors_to_campaigns::find('all', [
            'conditions' => ['campaign_id=?', $this->getCampaignId()],
            'include' => ['author'],
            'order' => 'id ASC',
        ]);

        $authors = [];

        foreach ($authors_association as $author_association) {

            $authors[] = $author_association->author;

        }

        return $authors ;

    }

    public function getAuthorsPublicCached(){

        $cache_key = 'getAuthorsCached::' . $this->getCampaignId();

        $cached = gdCache::get($cache_key);
        //$cached = false;

        if ($cached === false) {

            $authors = $this->getAuthors();

            $authors_public_info = [];

            foreach ($authors as $author)
                $authors_public_info[] = $author->getPublicInfo();

            gdCache::put($cache_key, json_encode($authors_public_info), 60);

        }
        else {

            $authors_public_info = json_decode($cached, true);

        }

        return $authors_public_info ;

    }

    public function getAuthorAssocitaionById($author_id){

        $authorAssociation = db_authors_to_campaigns::find('first', [
            'conditions' => ['author_id=? AND campaign_id=?', $author_id, $this->getCampaignId()],
        ]);

        return $authorAssociation;

    }

    public function setAuthors($authors_ids_new){

        $authors = $this->getAuthors();

        $isAnyDeleted = false;
        $isAnyInserted = false;

        foreach ($authors as $author){

            if (!in_array($author->id, $authors_ids_new)) {
                $this->removeAuthorAssociation($author->id);
                $isAnyDeleted = true;
            }

        }

        foreach ($authors_ids_new as $author_id)
            if ($this->addAuthorAssociation($author_id) !== false)
                $isAnyInserted = true;

        if ($isAnyInserted OR $isAnyDeleted)
            return true;

        return false;

    }

    public function addAuthorAssociation($author_id){

        $authorAssociation = $this->getAuthorAssocitaionById($author_id);

        if (!empty($authorAssociation))
            return false;

        $user_id = 0;

        if (is_authed()){
            Global $_USER ;
            $user_id = $_USER->getOptions('id');
        }

        $authorAssociationCreated = db_authors_to_campaigns::create([
            'author_id' => $author_id,
            'campaign_id' => $this->getCampaignId(),
            'created_by' => $user_id,
            'date' => 'now',
        ]);

        $this->flushCacheKeys();

        return $authorAssociationCreated;

    }

    public function removeAuthorAssociation($author_id){

        $authorAssociation = $this->getAuthorAssocitaionById($author_id);

        if (!empty($authorAssociation)) {
            $authorAssociation->delete();

            $this->flushCacheKeys();

            return true;
        }

        return false;

    }

    public function getLogChatId(){

        if ($this->domain == 'yabloko')
            return $_POST['botChannels']['yabloko_chat'];

        if ($this->domain == 'podpishi')
            return $_POST['botChannels']['petitions_chat'];

        return $_POST['botChannels']['my'] ;

    }

    public function setCenterCoordsByCity(){

        if ($this->petition_city == '')
            return false;

        $address = 'Россия, ' . $this->petition_city;

        $dadata = new dadata();

        $fields = ['query' => $address, 'count' => 1, 'locations' => ['city_type_full' => 'город']];
        $results = $dadata->suggest('address', $fields);

        $result = $results['suggestions'][0] ;
        
        if (!isset($result['data']['geo_lat']) OR $result['data']['geo_lon'] == '')
            return false;

        $this->center_lat = $result['data']['geo_lat'];
        $this->center_lng = $result['data']['geo_lon'];

        return true;

    }

    static function getIndexList($offset = 0, $limit = 5, $domain = ''){

        $campaigns = db_campaigns_list::find('all', [
            'conditions' => ['is_active=1 AND url NOT IN ("example", "sisters-sk") AND domain=? AND meta_image != "" AND is_confirmed=1 AND id NOT IN (54)', $domain],
            'select' => 'id, campaigns_list.url, domain, campaigns_list.meta_image, campaigns_list.subtitle_descr, campaigns_list.title, campaigns_list.petition_city, var_arrowcolor, date',
            'order' => 'date DESC',
            'limit' => $limit,
            'offset' => $offset,
        ]);


        foreach ($campaigns as $key => $campaign){

            $campaignPublicInfo = $campaign->getPublicInfo();

            $campaigns[$key] = $campaignPublicInfo ;

        }

        return $campaigns;

    }

    public function isReadonlyUrl(){

        $readonly = false;

        if ($this->url_opened_by != '')
            if ($this->url_opened_by == $this->url_readonly)
                $readonly = true;


        return $readonly;

    }

    public function getFullInfo(){

        $item = $this->getPublicInfo();

        $item['meta_image'] = $this->meta_image ;
        $item['meta_description'] = $this->meta_description ;
        $item['page_url'] = 'https://' . $this->getDomainName() . '/' . $this->getUrl() . '/yabloko' ;
        $item['url'] = $this->getUrl() ;
        $item['meta_title'] = $this->meta_title ;
        $item['onsave_descr'] = $this->onsave_descr ;

        $item['title'] = $this->title ;
        $item['appeal_title'] = $this->appeal_title ;
        $item['appeal_text'] = $this->appeal_text ;

        $item['petition_city'] = $this->petition_city ;

        $item['is_active'] = $this->is_active ;
        $item['is_appeal_editable'] = $this->is_appeal_editable ;
        $item['subtitle_descr'] = nl2br($this->subtitle_descr) ;
        $item['subtitle_title'] = $this->subtitle_title ;
        $item['whom'] = $this->whom ;
        $item['youtube_vid'] = $this->youtube_vid ;
        $item['onsave_popuptext'] = $this->onsave_popuptext ;

        $item['is_readonly'] = $this->isReadonlyUrl();

        $authors = $this->getAuthorsPublicCached();

        $item['authors'] = $authors;

        $item['author_exists'] = (empty($item['authors']) == false);

        return $item ;

    }


    public function isHostAppropriate($host){

        if ($host == 'podpishi.org')
            if ($this->domain == 'podpishi')
                return true ;

        if ($host == 'podpishi60.ru')
            if ($this->domain == 'yabloko')
                return true ;

        return false;

    }

    public function getAppealsAmount(){

        return self::getAppealsAmountById($this->getCampaignId());

    }

    static function getAppealsAmountById($campaign_id){

        return gdCache::closure('appealsAmountAtId::' . $campaign_id, function() use ($campaign_id){

            $amount = db_appeals_list::count([
                'conditions' => ['destination=?', $campaign_id],
            ]);

            return $amount;

        }, 3600 * 1);

    }

    public function getDateTs(){

        if (!is_object($this->date))
            return 0;

        return $this->date->format('U');

    }

    public function getDomainName(){

        $domain = '';

        if ($this->domain == 'yabloko')
            $domain = 'podpishi60.ru';

        if ($this->domain == 'podpishi')
            $domain = 'podpishi.org';


        return $domain;
    }

    public function getAbsUrl(){

        $domain = $this->getDomainName();

        if ($domain != '')
            $domain = 'https://' . $domain;

        return $domain . '/' . $this->getUrl();

    }

    public function getPublicInfo(){

        $imageUrl = '';
        if ($this->meta_image != '')
            $imageUrl = getImage($this->meta_image, 445, 245, true);

        $publish_date_h = gdHandlerSkeleton::getHumanDate($this->getDateTs()) . ' ' . date('Y', $this->getDateTs());

        $appeals_amount = 0;

        if ($this->__isset('appeals_amount'))
            $appeals_amount = $this->appeals_amount;

        if (!$this->__isset('appeals_amount'))
            $appeals_amount = $this->getAppealsAmount();

        $item = [
            'id' => $this->id,
            'arrowcolor' => '#ffc605',
            'image_preview_url' => $imageUrl,
            'url' => $this->getUrl(),
            'abs_url' => $this->getAbsUrl(),
            'title' => $this->title,
            'subtitle_descr' => $this->subtitle_descr,
            'appeals_amount' => $appeals_amount,
            'appeals_amount_skl' => getNumEnding($appeals_amount, ['подписал', 'подписало', 'подписали']),
            'date' => $this->getDateTs(),
            'publish_date_h' => $publish_date_h,
        ];

        if ($this->var_arrowcolor != '')
            $item['arrowcolor'] = $this->var_arrowcolor;

        return $item ;

    }

    public function after_save(){

        $this->flushCacheKeys();

    }

}

?>