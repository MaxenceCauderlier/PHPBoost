<?php

class FAQCategoryModel extends ORMModel
{

    protected static $primary_key = 'id';

    public function items() {
        return $this->has_many(FAQItemModel::class, 'itemsFAQ','id_category', 'id');
    }

    public static function get_table_name():string
    {
        return FaqSetup::$faq_cats_table;
    }

    /*
    public static function has_many()
    {
        return [
            [   
            'table' => FaqSetup::$faq_table,
            'name'  => 'items',
            'model' => 'FAQItemModel',
            'fields' => '*',
            'ON' =>
                [
                    [
                    'primary_key' => 'id', // pk of this model
                    'foreign_key' => 'id_category' // fk of the wanted model, here member
                    ]
                ]
            ]
        ];
    } */
}