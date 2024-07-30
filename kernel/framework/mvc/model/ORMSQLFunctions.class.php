<?php

class ORMSQLFunctions
{

    protected $querier;

    protected $table;

    protected $model;

    protected $statement;
    protected $statementType = null;

    protected $bind = [];

    protected $properties = [];

    protected $left_joins = [];

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    const STMT_SELECT = 'select';

    const STMT_SELECT_ONE = 'select_one';
    const STMT_COUNT = 'count';
    const STMT_INSERT = 'insert';
    const STMT_UPDATE = 'update';
    const STMT_DELETE = 'delete';
    const STMT_NULL = null;

    /**
     * Class constructor
     *
     * @param DBQuerier $querier The database querier
     * @param string $table The table name
     * @param string $model The model name
     */
    public function __construct(DBQuerier $querier, string $table, string $model)
    {
        $this->querier = $querier;
        $this->table = $table;
        $this->model = $model;
    }

    /**
     * Selects specific fields from the table.
     *
     * @param mixed ...$fields The fields to select as a list of strings, '*' for all fields.
     * @return $this The current instance of the class.
     */
    public function select(...$fields)
    {
        $this->statementType = self::STMT_SELECT;
        $this->statement = "SELECT ";

        // Add fields from belongs_to() to the list of fields to select, to generate LEFT JOINs
        foreach ($this->model::belongs_to() as $join) {
            $name_fk = $join['name'] ?? $join['table'];
            $left_fields = explode(',', $join['fields']);
            foreach ($left_fields as $left_field) {
                $fields[] = $name_fk . '.' . $left_field;
            }
        }
        for ($i = 0; $i < count($fields); $i++) {
            $this->statement .= $fields[$i] . ",";
        }
        $this->statement = rtrim($this->statement, ',') . " FROM " . $this->table . $this->compute_joins();
        return $this;
    }

    public function select_one_by_field(string $field, $value)
    {
        $this->select('*');
        $this->filter($field, '=', $value, 'WHERE');
        $this->statementType = self::STMT_SELECT_ONE;
        return $this;
    }

    public function raw(string $sql)
    {
        if ($this->statementType == self::STMT_NULL) {
            $this->statementType = self::STMT_SELECT;
        }
        $this->statement .= $sql;
        return $this;
    }

    /**
     * Counts the rows in the table.
     *
     * @param string $field The field to count. Defaults to '*' for all rows.
     * @return $this The current instance of the class.
     */
    public function count(string $field = '*')
    {
        $this->statementType = self::STMT_COUNT;
        $this->statement = "SELECT COUNT($field) FROM " . $this->table;
        return $this;
    }

    /**
     * Updates a row in the table with the given model data.
     *
     * @param ORMModel $model The model containing the data to update.
     * @return $this The current instance of the class.
     */
    public function update(ORMModel $model)
    {
        $this->statementType = self::STMT_UPDATE;
        $pk_field = $this->model::get_primary_key();
        $pk_value = $model->$pk_field;
        $this->filter($pk_field, '=', $pk_value, 'WHERE');
        $this->properties = $model->get_raw_properties();
        return $this;
    }

    /**
     * Inserts a new row into the table with the given ORMModel data.
     *
     * @param ORMModel $model The ORMModel containing the data to insert.
     * @return $this The current instance of the class.
     */
    public function insert(ORMModel $model)
    {
        $this->statementType = self::STMT_INSERT;
        $this->properties = $model->get_raw_properties();
        return $this;
    }

    /**
     * Deletes a row from the table based on the provided ORMModel.
     *
     * @param ORMModel $model The ORMModel containing the data to delete.
     * @return $this The current instance of the class.
     */
    public function delete(ORMModel $model)
    {
        $this->statementType = self::STMT_DELETE;
        $pk_field = $this->model::get_primary_key();
        $pk_value = $model->$pk_field;
        $this->filter($pk_field, '=', $pk_value, 'WHERE');
        return $this;
    }

    /**
     * Adds a WHERE clause to the SQL statement.
     *
     * @param string $field The field to compare.
     * @param string $operator The comparison operator, '=', '<', '>', '<=', '>=', '!=', 'LIKE'
     * @param mixed $value The value to compare against.
     * @return $this The current instance of the class.
     */
    public function where(string $field, string $operator, $value)
    {
        $this->filter($field, $operator, $value, 'WHERE');
        return $this;
    }

    /**
     * Adds an AND clause to the SQL statement.
     *
     * @param string $field The field to compare.
     * @param string $operator The comparison operator, '=', '<', '>', '<=', '>=', '!=', 'LIKE'
     * @param mixed $value The value to compare against.
     * @return $this The current instance of the class.
     */
    public function and(string $field, string $operator, $value)
    {
        $this->filter($field, $operator, $value, 'AND');
        return $this;
    }

    /**
     * Adds an OR clause to the SQL statement.
     *
     * @param string $field The field to compare.
     * @param string $operator The comparison operator, '=', '<', '>', '<=', '>=', '!=', 'LIKE'
     * @param mixed $value The value to compare against.
     * @return $this The current instance of the class.
     */
    public function or(string $field, string $operator, $value)
    {
        $this->filter($field, $operator, $value, 'OR');
        return $this;
    }

    public function order_by(string $field, string $direction = self::ORDER_ASC)
    {
        $this->statement .= " ORDER BY $field $direction";
        return $this;
    }

    public function limit(int $limit, int $offset = 0)
    {
        $this->statement .= " LIMIT $limit OFFSET $offset";
        return $this;
    }

    /**
     * Adds a clause to the SQL statement.
     *
     * @param string $field The field to compare.
     * @param string $operator The comparison operator, '=', '<', '>', '<=', '>=', '!=', 'LIKE'
     * @param mixed $value The value to compare against.
     * @param string $word The SQL word to use (WHERE, AND, OR or HAVING).
     * @return void
     */
    protected function filter(string $field, string $operator, $value,string $word):void
    {
        $this->add_bind($field, $value);

        // Add the WHERE, AND or OR clause to the SQL statement
        $this->statement .= " $word $field $operator :$field";
    }

    /**
     * Adds a field and value to the bind array, to prepare execution.
     * Binds are parameters in the SQL statement.
     *
     * @param string $field The field to add to the bind array.
     * @param mixed $value The value to add to the bind array. If it is a string, it will be enclosed in quotes.
     * @return void
     */
    protected function add_bind(string $field, $value)
    {
        if(gettype($value) == 'string') {
            $value = "'$value'";
        }
        $this->bind['fields'][] = $field;
        $this->bind['values'][] = $value;
    }

    /**
     * Executes a SQL statement with the given properties.
     *
     * @param array $properties An array of properties to bind to the statement.
     * @return mixed The result of the executed statement.
     */
    public function execute(array $properties = [])
    {
        // Bind each property to the statement
        foreach ($properties as $key => $value)
        {
            $this->add_bind($key, $value);
        }

        $this->left_joins = $this->model::belongs_to() ?? [];
        switch($this->statementType)
        {
            case self::STMT_SELECT:
                $result = $this->process_select();
                break;
            case self::STMT_SELECT_ONE:
                // Process like a select many 
                $set = $this->process_select();
                $iterator = $set->getIterator();
                // And get only the 1st result
                $result = $set[$iterator->key()];
                break;
            case self::STMT_COUNT:
                $result = $this->querier->select($this->statement, $this->get_binds(), SelectQueryResult::FETCH_NUM);
                $result = $result->fetch()[0];
                break;
            case self::STMT_UPDATE:
                $result = $this->querier->update($this->table, $this->properties, $this->statement, $this->get_binds())->get_affected_rows();
                break;
            case self::STMT_INSERT:
                $result = $this->querier->insert($this->table, $this->properties)->get_last_inserted_id();
                break;
            case self::STMT_DELETE:
                $this->querier->delete($this->table, $this->statement, $this->get_binds());
                break;
        }
        // Reset the statement, statement type, left joins, properties, and binds
        $this->statement = '';
        $this->statementType = self::STMT_NULL;
        $this->left_joins = [];
        $this->properties = [];
        $this->bind = [];

        return $result ?? null;
    }

    /**
     * Executes a SELECT query and returns the result set.
     *
     * @return ORMResultsSet The result set of the SELECT query.
     */
    private function process_select()
    {
        $result = $this->querier->select($this->statement, $this->get_binds(), SelectQueryResult::FETCH_ASSOC);
        $set = new ORMResultsSet($result, $this->model);
        foreach ($this->left_joins as $join)
        {
            $fields = [];
            // table_fk is the name of the foreign table
            $table_fk = $join['table'];
            // name_fk is the name of the foreign key. Submodel will be accessible by this name
            $name_fk = $join['name'] ?? $table_fk;
            $class_name = $join['model'] ?? 'stdClass';
            // Get the fields of the foreign table
            $request = "SHOW COLUMNS FROM $table_fk";
            $fields_result =  $this->querier->select($request, [], SelectQueryResult::FETCH_ASSOC);
            while ($row = $fields_result->fetch())
            {
                $fields[] = $row['Field'];
            }
            $set->create_sub_model($fields, $class_name, $name_fk);
        }
        return $set;
    }

    /**
     * Retrieves the binds from the current object.
     *
     * @return array The binds as an associative array.
     */
    private function get_binds()
    {
        $bind = [];
        if (!empty($this->bind))
        {
            $i = count($this->bind['fields']);
            for($j = 0; $j < $i; $j ++) {
                $bind[$this->bind['fields'][$j]] = $this->bind['values'][$j];
            }
        }
        return $bind;
    }

    /**
     * Computes the SQL statement for left joins based on the model's belongs_to() method.
     * belongs_to is used to generate LEFT JOIN statements.
     *
     * @return string The SQL statement for left joins.
     */
    private function compute_joins()
	{
        if ($this->model::belongs_to() === false)
        {
            return '';
        }
        $stmt = '';
        foreach ($this->model::belongs_to() as $join)
        {
            $table_fk = $join['table'];
            // if name_fk is not set, use table_fk for name
            $name_fk = $join['name'] ?? $table_fk;
            $stmt = " LEFT JOIN $table_fk $name_fk ON ";
            foreach ($join['ON'] as $on)
            {
                $stmt .= $name_fk . '.' . $on['foreign_key'] . ' = ' . $this->table . '.' . $on['primary_key'] . ' AND';
            }
            $stmt = rtrim($stmt, ' AND');
        }
        return $stmt;
	}
}