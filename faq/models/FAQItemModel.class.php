<?php

class FAQItemModel extends ORMModel
{

    protected static $primary_key = 'id';

    public static function get_table_name():string
    {
        return FaqSetup::$faq_table;
    }

    public function __construct($attributes = [])
    {
        //$this->belongs_to('FAQCategoryModel', 'category', 'id_category', 'id');
        $this->belongs_to('UserModel', 'author', 'author_user_id', 'user_id');
        parent::__construct($attributes);
    }

    public function category()
    {
        return $this->belongs_to('FAQCategoryModel', 'category', 'id_category', 'id');
    }

    /*
    public static function belongs_to()
    {
        return [
            [   
            'table' => DB_TABLE_MEMBER,
            'name'  => 'member',
            'model' => 'UserModel',
            'fields' => '*',
            'ON' =>
                [
                    [
                    'primary_key' => 'author_user_id', // pk of this model
                    'foreign_key' => 'user_id' // fk of the wanted model, here member
                    ]
                ]
            ]
        ];
    } */
}