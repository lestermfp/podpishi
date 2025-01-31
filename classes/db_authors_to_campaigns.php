<?php

class db_authors_to_campaigns extends ActiveRecord\Model {
    static $table_name = 'authors_to_campaigns';

    static $belongs_to = [
        [
            'author',
            'class_name' => 'db_authors_list',
            'foreign_key' => 'author_id',
        ],
        [
            'campaign',
            'class_name' => 'db_campaigns_list',
            'foreign_key' => 'campaign_id',
        ],
    ];

}

?>