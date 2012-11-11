<?php
namespace Wusa\Db\Metadata\Object;
/**
 *
 * @author lukas
 */
class IndexObject
{
    /**
     *
     * @var string
     */
    protected $name = null;

    /**
     *
     * @var string
     */
    protected $tableName = null;

    /**
     *
     * @var string
     */
    protected $schemaName = null;

    /**
     * One of "PRIMARY KEY", "UNIQUE", "FOREIGN KEY", or "CHECK"
     *
     * @var string
     */
    protected $type = null;

    /**
     * @var string[]
     */
    protected $columns = array();

    /**
     * @var bool
     */
    protected $unique = NULL;

    /**
     * @var string
     */
    protected $comment = null;
    /**
     * Constructor
     *
     * @param string $name
     * @param string $tableName
     * @param string $schemaName
     */
    public function __construct($name, $tableName, $schemaName = null)
    {
        $this->setName($name);
        $this->setTableName($tableName);
        $this->setSchemaName($schemaName);
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set schema name
     *
     * @param string $schemaName
     */
    public function setSchemaName($schemaName)
    {
        $this->schemaName = $schemaName;
    }

    /**
     * Get schema name
     *
     * @return string
     */
    public function getSchemaName()
    {
        return $this->schemaName;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param  string $tableName
     * @return ConstraintObject
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function hasColumns()
    {
        return (!empty($this->columns));
    }

    /**
     * Get Columns.
     *
     * @return string[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set Columns.
     *
     * @param string[] $columns
     * @return ConstraintObject
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Is unique index
     *
     * @return boolean
     */
    public function isUnique()
    {
        return $this->unique;
    }
    /**
     * Is unique index
     *
     * @return boolean
     */
    public function getUnique()
    {
        return $this->unique;
    }

    /**
     * Sets if the Index is unique
     * @param $set
     */
    public function setUnique($set)
    {
        $this->unique= (bool)$set;
    }

    /**
     * @param $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * If the Index is a Primary key
     * @return bool
     */
    public function isPrimaryKey()
    {
        return $this->name == 'PRIMARY';
    }
}
