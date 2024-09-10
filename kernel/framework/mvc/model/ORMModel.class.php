<?php

abstract class ORMModel
{
    protected static $primary_key = 'id';
    protected $query = '';
    protected $bindings = [];
    protected $relations = [];

    protected $attributes = [];

    protected $own_fields = [];

    abstract public static function get_table_name(): string;

    /**
     * Initializes the ORMModel object.
     *
     * @return void
     */
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns the database querier.
     *
     * @return DBQuerier the database querier
     */
    protected function get_querier(): DBQuerier
    {
        return PersistenceContext::get_querier();
    }

    /**
     * Adds a belongs_to relationship to the model.
     *
     * @param string $related_model The class name of the related model.
     * @param string $foreign_key The foreign key column name in the main model
     * @param string|null $local_key The local key column name in the related model. Defaults to 'id'.
     * @return self
     */
    public function belongs_to(string $related_model, string $owner_name, string $foreign_key, string $local_key = 'id'): self
    {
        $this->relations[] = [
            'type' => 'belongs_to',
            'model' => $related_model,
            'foreign_key' => $foreign_key,
            'local_key' => $local_key,
            'owner_name' => $owner_name
        ];
        return $this;
    }

    /**
     * Adds a has_many relationship to the model.
     *
     * @param string $related_model The class name of the related model.
     * @param string $items_name The name of the property that will contain the items.
     * @param string $foreign_key The foreign key column name.
     * @param string|null $local_key The local key column name. Defaults to the primary key.
     * @return self
     */
    public function has_many(string $related_model, string $items_name, string $foreign_key, $local_key = false): self
    {
        if ($local_key === false) {
            $local_key = static::$primary_key;
        }
        $this->relations[] = [
            'type' => 'has_many',
            'model' => $related_model,
            'foreign_key' => $foreign_key,
            'local_key' => $local_key,
            'items_name' => $items_name
        ];
        return $this;
    }

    /**
     * Adds a has_many_through relationship to the model.
     *
     * @param string $related_model The class name of the related model.
     * @param string $through_model The class name of the through model.
     * @param string $items_name The name of the property that will contain the items.
     * @param string $first_key The key on the through model that references the current model.
     * @param string $second_key The key on the through model that references the related model.
     * @param string|null $local_key The local key column name. Defaults to the primary key.
     * @param string|null $second_local_key The local key column name of the related model. Defaults to the primary key.
     * @return self
     */
    public function has_many_through(string $related_model, string $through_model, string $items_name, $first_key, $second_key, $local_key = false, $second_local_key = false): self
    {
        if ($local_key === false) {
            $local_key = static::$primary_key;
        }
        if ($second_local_key === false) {
            $second_local_key = $related_model::$primary_key;
        }
        $this->relations[] = [
            'type' => 'has_many_through',
            'model' => $related_model,
            'through' => $through_model,
            'first_key' => $first_key,
            'second_key' => $second_key,
            'local_key' => $local_key,
            'second_local_key' => $second_local_key,
            'items_name' => $items_name
        ];
        return $this;
    }

    /**
     * Adds a has_one relationship to the model.
     *
     * @param string $related_model The class name of the related model.
     * @param string $foreign_key The foreign key column name.
     * @param string|null $local_key The local key column name. Defaults to the primary key.
     * @return self
     */
    public function has_one(string $related_model, string $item_name, string $foreign_key, $local_key = false): self
    {
        if ($local_key === false) {
            $local_key = static::$primary_key;
        }
        $this->relations[] = [
            'type' => 'has_one',
            'model' => $related_model,
            'foreign_key' => $foreign_key,
            'local_key' => $local_key,
            'item_name' => $item_name
        ];
        return $this;
    }

    /**
     * Execute the query and retrieve the results with relations.
     *
     * This function executes the query stored in the $this->query property and
     * retrieves the results. It then creates objects of the current class and
     * populates their attributes with the query results. If there are any relations
     * defined in the $this->relations property, it will load them using eager
     * loading.
     *
     * @return array[ORMModel] An array of objects of the current class.
     */
    public function get(): array
    {
        $this->query = (stripos($this->query, 'SELECT') === false) ? "SELECT * FROM " . static::get_table_name() . $this->query : $this->query;
        $results = self::get_querier()->select($this->query, $this->bindings);
        $class_name = get_called_class();
        $objects = [];
        while ($row = $results->fetch()) {
            $obj = new $class_name();
            foreach ($row as $key => $value) {
                $obj->own_fields[$key] = $key;
                $obj->attributes[$key] = $value;
            }
            $objects[] = $obj;
        }

        if (!empty($this->relations)) {
            $this->load_eager_relations($objects);
        }

        $this->reset();
        $results->dispose();
        return $objects;
    }

    /**
     * Load relations using eager loading for a set of objects.
     *
     * @param array[ORMModel] $objects The objects to load relations for.
     * @return void
     */
    protected function load_eager_relations(&$objects)
    {
        foreach ($this->relations as $relation) {
            switch ($relation['type']) {
                case 'belongs_to':
                    $this->load_belongs_to_relation($objects, $relation);
                    break;
                case 'has_many':
                    $this->load_has_many_relation($objects, $relation);
                    break;
                case 'has_one':
                    $this->load_has_one_relation($objects, $relation);
                    break;
                case 'has_many_through':
                    $this->load_has_many_through_relation($objects, $relation);
                    break;
            }
        }
    }

    /**
     * Load a belongs_to relation using eager loading for a set of objects.
     *
     * @param array[ORMModel] &$objects The objects to load the relation for.
     * @param array $relation The relation to load.
     * @return void
     */
    protected function load_belongs_to_relation(&$objects, $relation)
    {
        $related_model = new $relation['model']();
        $foreign_keys = array_unique(array_map(function ($result) use ($relation) {
            return $result->{$relation['foreign_key']};
        }, $objects));

        if (empty($foreign_keys)) {
            return;
        }

        $related_records = $related_model->where_in($relation['local_key'], $foreign_keys)->get();

        $related_map = [];
        foreach ($related_records as $record) {
            $related_map[$record->{$relation['local_key']}] = $record;
        }

        foreach ($objects as $result) {
            $foreign_key_value = $result->{$relation['foreign_key']};
            $result->attributes[$relation['owner_name']] = $related_map[$foreign_key_value] ?? null;
        }
    }

    // Méthode pour charger une relation has_many avec eager loading
    protected function load_has_many_relation(&$objects, $relation)
    {
        $related_model = new $relation['model']();
        $local_keys = array_unique(array_map(function ($result) use ($relation) {
            return $result->attributes[$relation['local_key']];
        }, $objects));

        if (empty($local_keys)) {
            return;
        }

        /**
         * @var array[ORMModel] $related_records
         */
        $related_records = $related_model->where_in($relation['foreign_key'], $local_keys)->get();

        $related_map = [];
        foreach ($related_records as $record) {
            $related_map[$record->attributes[$relation['foreign_key']]][] = $record;
        }
        foreach ($objects as $result) {
            $local_key_value = $result->attributes[$relation['local_key']];
            $result->attributes[$relation['items_name']] = $related_map[$local_key_value] ?? [];
        }
    }

    // Méthode pour charger une relation has_one avec eager loading
    protected function load_has_one_relation(&$objects, $relation)
    {
        /**
         * @var ORMModel $related_model
         */
        $related_model = new $relation['model']();
        $local_keys = array_unique(array_map(function ($result) use ($relation) {
            return $result->attributes[$relation['local_key']];
        }, $objects));

        if (empty($local_keys)) {
            return;
        }

        /**
         * @var array[ORMModel] $related_records
         */
        $related_records = $related_model->where_in($relation['foreign_key'], $local_keys)->get();

        $related_map = [];
        foreach ($related_records as $record) {
            $related_map[$record->{$relation['foreign_key']}] = $record;
        }

        foreach ($objects as $result) {
            $local_key_value = $result->{$relation['local_key']};
            $result->attributes[$relation['item_name']] = $related_map[$local_key_value] ?? null;
        }
    }

    // Méthode pour charger une relation has_many_through avec eager loading
    protected function load_has_many_through_relation(&$objects, $relation)
    {
        $through_model = new $relation['through']();
        $local_keys = array_unique(array_map(function ($result) use ($relation) {
            return $result->attributes[$relation['local_key']];
        }, $objects));

        if (empty($local_keys)) {
            return;
        }

        $through_records = $through_model->where_in($relation['first_key'], $local_keys)->get();

        $second_keys = array_unique(array_map(function ($through_record) use ($relation) {
            return $through_record->{$relation['second_key']};
        }, $through_records));

        if (empty($second_keys)) {
            return;
        }

        $related_model = new $relation['model']();
        $related_records = $related_model->where_in($relation['second_local_key'], $second_keys)->get();

        $through_map = [];
        foreach ($through_records as $through_record) {
            $through_map[$through_record->{$relation['first_key']}][] = $through_record->{$relation['second_key']};
        }

        $related_map = [];
        foreach ($related_records as $related_record) {
            $related_map[$related_record->{$relation['second_local_key']}] = $related_record;
        }

        foreach ($objects as $result) {
            $local_key_value = $result->{$relation['local_key']};
            $result->attributes[$relation['items_name']] = [];

            if (isset($through_map[$local_key_value])) {
                foreach ($through_map[$local_key_value] as $second_key) {
                    if (isset($related_map[$second_key])) {
                        $result->attributes[$relation['items_name']][] = $related_map[$second_key];
                    }
                }
            }
        }
    }



    // Méthode pour récupérer un seul résultat
    public function first(): ?self
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public function last(): ?self
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[count($results) - 1] : null;
    }
    


    public function find($id): ?self
    {
        return $this->find_by(static::$primary_key, $id);
    }

    public function find_by(string $field, $value): ?self
    {
        return $this->where($field, '=', $value)->first();
    }

    public function count()
    {
        $this->query = "SELECT COUNT(*) FROM " . static::get_table_name() . $this->query;
        $res = (int) $this->first()->attributes['COUNT(*)'];
        $this->reset();
        return $res;
    }

    public function save()
    {
        if (isset($this->attributes[static::$primary_key])) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    // Méthode pour insérer un nouvel enregistrement
    protected function insert()
    {
        $result = self::get_querier()->insert(static::get_table_name(), $this->get_own_fields());
        $this->attributes[static::$primary_key] = $result->get_last_inserted_id();
        return $this;
    }

    // Méthode pour mettre à jour un enregistrement
    protected function update()
    {
        self::get_querier()->update(
            static::get_table_name(),
            $this->get_own_fields(),
            'WHERE ' . static::$primary_key . '=:id',
            ['id' => $this->attributes[static::$primary_key]]
        );
        return $this;
    }

    public function delete()
    {
        if (isset($this->attributes[static::$primary_key])) {
            self::get_querier()->delete(static::get_table_name(), 'WHERE ' . static::$primary_key . '=:id', ['id' => $this->attributes[static::$primary_key]]);
            $this->reset();
            return true;
        }
        return false;
    }

    public function get_own_fields():array
    {
        $attr = [];
        foreach ($this->own_fields as $key) {
            $attr[$key] = $this->attributes[$key];
        }
        return $attr;
    }

    /**
     * Adds a WHERE clause to the query
     *
     * @param string $field The field to filter on
     * @param string $operator The operator to use for the filter
     * @param mixed $value The value to filter on
     *
     * @return ORMModel The current instance
     */
    public function where(string $field, string $operator, $value):self
    {
        if (substr($this->query, -1) !== '(')
        {
            // Open parenthesis
            $this->query .= (stripos($this->query, 'WHERE') === false) ? " WHERE " : " AND ";
        }
        $this->query .= "$field $operator :$field";
        $this->bindings[$field] = $value;
        return $this;
    }

    /**
     * Adds a WHERE IN clause to the query
     *
     * @param string $field The field to filter on
     * @param array $values The values to filter on
     *
     * @return ORMModel The current instance
     */
    public function where_in(string $field, array $values):self
    {
        $placeholders = implode(',', $values);
        if (substr($this->query, -1) !== '(')
        {
            // Open parenthesis
            $this->query .= (stripos($this->query, 'WHERE') === false) ? " WHERE " : " AND ";
        }
        $this->query .= "$field IN ($placeholders)";
        return $this;
    }

    public function or_where(string $field, string $operator, $value):self
    {
        $this->query .= " OR $field $operator :$field";
        $this->bindings[$field] = $value;
        return $this;
    }

    public function or_where_in(string $field, array $values):self
    {
        $placeholders = implode(',', $values);
        $this->query .= " OR $field IN ($placeholders)";
        return $this;
    }

    // Méthode pour débuter un groupe de conditions (parenthèses ouvrantes)
    public function group_start():self
    {
        $this->query .= (stripos($this->query, 'WHERE') === false) ? " WHERE (" : " AND (";
        return $this;
    }

    // Méthode pour débuter un groupe de conditions avec un OR (parenthèses ouvrantes)
    public function or_group_start():self
    {
        $this->query .= " OR (";
        return $this;
    }

    // Méthode pour terminer un groupe de conditions (parenthèses fermantes)
    public function group_end():self
    {
        $this->query .= ")";
        return $this;
    }

    public function order_by(string $field, $direction = 'ASC'):self
    {
        $this->query .= " ORDER BY $field $direction";
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER')
    {
        $this->query .= " $type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function group_by($field)
    {
        $this->query .= " GROUP BY $field";
        return $this;
    }

    // Méthode pour ajouter une condition HAVING
    public function having($field, $operator, $value)
    {
        $this->query .= " HAVING $field $operator :$field";
        $this->bindings[$field] = $value;
        return $this;
    }

    // Méthode pour ajouter une limite LIMIT
    public function limit($number)
    {
        $this->query .= " LIMIT $number";
        return $this;
    }

    // Méthode pour ajouter un offset OFFSET
    public function offset($number)
    {
        $this->query .= " OFFSET $number";
        return $this;
    }

    public function reset()
    {
        $this->query = '';
        $this->bindings = [];
        $this->relations = [];
    }

    public function __set($name, $value)
    {
        if (method_exists($this, "set_$name")) 
        {
            $this->{"set_$name"}($value);
        } else 
        {
            $this->attributes[$name] = $value;
        }

    }

    public function __get($name)
    {
        if (method_exists($this, "get_$name"))
        {
            return $this->{"get_$name"}();
        }
        return $this->attributes[$name] ?? null;
    }



}
