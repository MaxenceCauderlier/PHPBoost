<?php

class UserModel extends ORMModel
{


    protected static $primary_key = 'user_id';

    public static function get_table_name():string
    {
        return PREFIX . 'member';
    }

    public function authentication_method()
    {
        return $this->has_one('auth', 'authentication_methodddd', 'user_id');
    }

    public function get_display_name()
    {
        return $this->attributes['display_name'] . '_ppppppppp';
    }
}