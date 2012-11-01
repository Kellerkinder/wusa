<?php
namespace Wusa\Db\Metadata\Source;
use Zend\Db\Adapter\Adapter;
use Wusa\Db\Metadata\Object;
/**
 *
 * @author lukas
 */
class MysqlMetadata extends \Zend\Db\Metadata\Source\MysqlMetadata
{
    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
    }

    protected function loadSchemaData()
    {
        if (isset($this->data['schemas'])) {
            return;
        }
        $this->prepareDataHierarchy('schemas');

        $p = $this->adapter->getPlatform();

        $sql = 'SELECT ' . $p->quoteIdentifier('SCHEMA_NAME')
            . ' FROM ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA', 'SCHEMATA'))
            . ' WHERE ' . $p->quoteIdentifier('SCHEMA_NAME')
            . ' != ' . $p->quoteValue('INFORMATION_SCHEMA');

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);

        $schemas = array();
        foreach ($results->toArray() as $row) {
            $schemas[] = $row['SCHEMA_NAME'];
        }

        $this->data['schemas'] = $schemas;
    }

    protected function loadTableNameData($schema)
    {
        if (isset($this->data['table_names'][$schema])) {
            return;
        }
        $this->prepareDataHierarchy('table_names', $schema);

        $p = $this->adapter->getPlatform();

        $isColumns = array(
            array('T','TABLE_NAME'),
            array('T','TABLE_TYPE'),
            array('V','VIEW_DEFINITION'),
            array('V','CHECK_OPTION'),
            array('V','IS_UPDATABLE'),
            array('T','TABLE_COMMENT'),
            array('T','ENGINE'),
        );

        array_walk($isColumns, function (&$c) use ($p) { $c = $p->quoteIdentifierChain($c); });

        $sql = 'SELECT ' . implode(', ', $isColumns)
            . ' FROM ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','TABLES')) . 'T'

            . ' LEFT JOIN ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','VIEWS')) . ' V'
            . ' ON ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
            . '  = ' . $p->quoteIdentifierChain(array('V','TABLE_SCHEMA'))
            . ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_NAME'))
            . '  = ' . $p->quoteIdentifierChain(array('V','TABLE_NAME'))

            . ' WHERE ' . $p->quoteIdentifierChain(array('T','TABLE_TYPE'))
            . ' IN (' . $p->quoteValueList(array('BASE TABLE', 'VIEW')) . ')';

        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                . ' = ' . $p->quoteValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                . ' != ' . $p->quoteValue('INFORMATION_SCHEMA');
        }

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);

        $tables = array();
        foreach ($results->toArray() as $row) {
            $tables[$row['TABLE_NAME']] = array(
                'table_type' => $row['TABLE_TYPE'],
                'view_definition' => $row['VIEW_DEFINITION'],
                'check_option' => $row['CHECK_OPTION'],
                'is_updatable' => ('YES' == $row['IS_UPDATABLE']),
                'comment' => $row['TABLE_COMMENT'],
                'engine'  => $row['ENGINE'],
            );
        }

        $this->data['table_names'][$schema] = $tables;
    }

    protected function loadColumnData($table, $schema)
    {
        if (isset($this->data['columns'][$schema][$table])) {
            return;
        }
        $this->prepareDataHierarchy('columns', $schema, $table);
        $p = $this->adapter->getPlatform();

        $isColumns = array(
            array('C','ORDINAL_POSITION'),
            array('C','COLUMN_DEFAULT'),
            array('C','IS_NULLABLE'),
            array('C','DATA_TYPE'),
            array('C','CHARACTER_MAXIMUM_LENGTH'),
            array('C','CHARACTER_OCTET_LENGTH'),
            array('C','NUMERIC_PRECISION'),
            array('C','NUMERIC_SCALE'),
            array('C','COLUMN_NAME'),
            array('C','COLUMN_TYPE'),
            array('C','COLUMN_COMMENT'),
        );

        array_walk($isColumns, function (&$c) use ($p) { $c = $p->quoteIdentifierChain($c); });

        $sql = 'SELECT ' . implode(', ', $isColumns)
            . ' FROM ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','TABLES')) . 'T'
            . ' INNER JOIN ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','COLUMNS')) . 'C'
            . ' ON ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
            . '  = ' . $p->quoteIdentifierChain(array('C','TABLE_SCHEMA'))
            . ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_NAME'))
            . '  = ' . $p->quoteIdentifierChain(array('C','TABLE_NAME'))
            . ' WHERE ' . $p->quoteIdentifierChain(array('T','TABLE_TYPE'))
            . ' IN (' . $p->quoteValueList(array('BASE TABLE', 'VIEW')) . ')'
            . ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_NAME'))
            . '  = ' . $p->quoteValue($table);

        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                . ' = ' . $p->quoteValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                . ' != ' . $p->quoteValue('INFORMATION_SCHEMA');
        }

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $columns = array();
        foreach ($results->toArray() as $row) {
            $erratas = array();
            $matches = array();
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];
                if (preg_match_all("/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/", $permittedValues, $matches, PREG_PATTERN_ORDER)) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = array($permittedValues);
                }
                $erratas['permitted_values'] = $permittedValues;
            }
            $columns[$row['COLUMN_NAME']] = array(
                'ordinal_position'          => $row['ORDINAL_POSITION'],
                'column_default'            => $row['COLUMN_DEFAULT'],
                'is_nullable'               => ('YES' == $row['IS_NULLABLE']),
                'data_type'                 => $row['DATA_TYPE'],
                'character_maximum_length'  => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length'    => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision'         => $row['NUMERIC_PRECISION'],
                'numeric_scale'             => $row['NUMERIC_SCALE'],
                'numeric_unsigned'          => (false !== strpos($row['COLUMN_TYPE'], 'unsigned')),
                'erratas'                   => $erratas,
                'comment'                  => $row['COLUMN_COMMENT'],
            );
        }

        $this->data['columns'][$schema][$table] = $columns;
    }
    /**
     * Get table
     *
     * @param  string $tableName
     * @param  string $schema
     * @return Object\TableObject
     */
    public function getTable($tableName, $schema = null)
    {
        if ($schema === null) {
            $schema = $this->defaultSchema;
        }

        $this->loadTableNameData($schema);

        if (!isset($this->data['table_names'][$schema][$tableName])) {
            throw new \Exception('Table "' . $tableName . '" does not exist');
        }

        $data = $this->data['table_names'][$schema][$tableName];
        switch ($data['table_type']) {
            case 'BASE TABLE':
                $table = new Object\TableObject($tableName);
                break;
            case 'VIEW':
                $table = new Object\ViewObject($tableName);
                $table->setViewDefinition($data['view_definition']);
                $table->setCheckOption($data['check_option']);
                $table->setIsUpdatable($data['is_updatable']);
                break;
            default:
                throw new \Exception('Table "' . $tableName . '" is of an unsupported type "' . $data['table_type'] . '"');
        }
        $table->setComment($data['comment']);
        $table->setColumns($this->getColumns($tableName, $schema));
        return $table;
    }

    /**
     * Get column
     *
     * @param  string $columnName
     * @param  string $table
     * @param  string $schema
     * @return Object\ColumnObject
     */
    public function getColumn($columnName, $table, $schema = null)
    {
        if ($schema === null) {
            $schema = $this->defaultSchema;
        }

        $this->loadColumnData($table, $schema);

        if (!isset($this->data['columns'][$schema][$table][$columnName])) {
            throw new \Exception('A column by that name was not found.');
        }

        $info = $this->data['columns'][$schema][$table][$columnName];

        $column = new Object\ColumnObject($columnName, $table, $schema);
        $props = array(
            'ordinal_position', 'column_default', 'is_nullable',
            'data_type', 'character_maximum_length', 'character_octet_length',
            'numeric_precision', 'numeric_scale', 'numeric_unsigned',
            'erratas', 'comment'
        );
        foreach ($props as $prop) {
            if (isset($info[$prop])) {
                $column->{'set' . str_replace('_', '', $prop)}($info[$prop]);
            }
        }

        $column->setOrdinalPosition($info['ordinal_position']);
        $column->setColumnDefault($info['column_default']);
        $column->setIsNullable($info['is_nullable']);
        $column->setDataType($info['data_type']);
        $column->setCharacterMaximumLength($info['character_maximum_length']);
        $column->setCharacterOctetLength($info['character_octet_length']);
        $column->setNumericPrecision($info['numeric_precision']);
        $column->setNumericScale($info['numeric_scale']);
        $column->setNumericUnsigned($info['numeric_unsigned']);
        $column->setErratas($info['erratas']);

        return $column;
    }
    private function quoteValue(&$value)
    {
        $p = $this->adapter->getPlatform();
        $value = $p->quoteValue($value);
    }
    public function createTable(Object\TableObject $table)
    {
        $p = $this->adapter->getPlatform();
        $coldefinition = array();
        foreach($table->getColumns() as $column)
        {
            $col = "\t".$column->getName().' '.$column->getDataType();
            if($column->getCharacterMaximumLength() && !($column->getDataType() == 'enum' || $column->getDataType() == 'set'))
                $col .= ' ('.$column->getCharacterMaximumLength().') ';
            if($column->getNumericPrecision())
                $col .= ' ('.$column->getNumericPrecision().') ';
            if($column->getNumericScale())
                $col .= ' ('.$column->getNumericScale().') ';
            if($column->getErrata('permitted_values'))
            {
                $errata = $column->getErrata('permitted_values');
                array_walk($errata,array($this,'quoteValue'));
                $col .= ' ('.implode(',',$errata).') ';
            }
            if($column->getNumericUnsigned())
                $col .= ' unsigned ';
            if($column->getIsNullable())
                $col .= ' NULL ';
            else
                $col .= ' NOT NULL ';
            if($column->getColumnDefault())
                $col .= ' DEFAULT '.(is_numeric($column->getColumnDefault())?$column->getColumnDefault():'"'.$column->getColumnDefault().'"').' ';
            if($column->getComment())
                $col .= ' COMMENT \''.$column->getComment().'\' ';
            //$col .= ",\n";
            $coldefinition[] = $col;
        }
        $sql = 'CREATE TABLE '.$table->getName(). " ( \n";
        $sql .= implode(",\n",$coldefinition)."\n";
        $sql .= ' ) ';
        if($table->getEngine())
        {
            $sql .= ' ENGINE = \''.$table->getEngine().'\'';
        }
        if($table->getComment())
        {
            $sql .= ' COMMENT = "'.$table->getComment().'"';
        }
        //echo $sql.';';
        return $this->adapter->query($sql,Adapter::QUERY_MODE_EXECUTE);
    }
}
