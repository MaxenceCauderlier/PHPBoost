<?php

class auth extends ORMModel {

    protected static $primary_key = 'user_id';
    public static function get_table_name():string {
        return PREFIX . 'authentication_method';
    }
}