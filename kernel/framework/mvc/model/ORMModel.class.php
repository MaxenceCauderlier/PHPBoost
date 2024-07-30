<?php

abstract class ORMModel
{
    protected static $_connection = null;
    protected static $_functions = null;

    protected $_properties = [];

    abstract public static function get_table_name();

    public static function get_primary_key()
    {
        return 'id';
    }

    public static function has_many()
    {
        return false;
    }

    public static function belongs_to()
    {
        return false;
    }

    public static function has_one()
    {
        return false;
    }

    public static function find_by_pk($value)
    {
        return static::find_by_field(static::get_primary_key(), $value);
    }

    public static function find_by_field(string $field, $value)
    {
        static::get_functions()->select_one_by_field($field, $value);
        return static::$_functions;
    }

    public static function all()
    {
        static::get_functions()->select('*');
        return static::$_functions;
    }

    public static function raw($query)
    {
        static::get_functions()->raw($query);
        return static::$_functions;
    }

    public static function select(...$fields)
    {
        if (count($fields) === 0)
        {
            $fields = ['*'];
        }
        static::get_functions()->select(...$fields);
        return static::$_functions;
    }

    public static function count(string $field = '*')
    {
        static::get_functions()->count($field);
        return static::$_functions;
    }

    protected static function get_querier()
    {
        return PersistenceContext::get_querier();
    }

    protected static function get_functions()
    {
        if (static::$_functions === null)
        {
            static::$_functions = new ORMSQLFunctions(static::get_querier(), static::get_table_name(), static::class);
        }
        return static::$_functions;
    }

    public function save()
    {
        if (isset($this->_properties[static::get_primary_key()]))
        {
            // If there is a value for the primary key, it's an update
            // Return the last inserted pk (ID)
            return static::get_functions()->update($this)->execute();
        }
        // Return int of rows affected
        return static::get_functions()->insert($this)->execute();
    }

    public function delete()
    {
        return static::get_functions()->delete($this)->execute();
    }

    public function get_raw_properties()
    {
        $own_properties = [];
        foreach ($this->_properties as $key => $value)
        {
            if (!is_object($value))
            {
                $own_properties[$key] = $value;
            }
        }
        return $own_properties;
    }

    public function __set($name, $value)
    {
        $this->_properties[$name] = $value;
    }

    public function __get($name)
    {
        return $this->_properties[$name] ?? null;
    }

    public function __unset($name)
    {
        if (isset($this->_properties[$name]))
        {
            unset($this->_properties[$name]);
        }
    }
    


}