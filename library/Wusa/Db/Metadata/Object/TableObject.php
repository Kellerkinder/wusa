<?php
namespace Wusa\Db\Metadata\Object;
/**
 *
 * @author Lukas Plattner
 */
class TableObject extends \Zend\Db\Metadata\Object\TableObject
{
    /**
     * @var string
     */
    protected $comment = '';
    /**
     * @var string
     */
    protected $engine = 'InnoDB';
    /**
     * The Indexs of that Tables
     * @var array
     */
    protected $indexes = array();
    /**
     * Sets the Comment
     * @param $com
     */
    public function setComment($com)
    {
        $this->comment = $com;
    }
    /**
     * Returns the comment
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    /**
     * Sets Engine
     * @param $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }
    /**
     * Gets Engine
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Adds an Index
     * @param $index
     */
    public function addIndex($index)
    {
        $this->indexes[] = $index;
    }

    /**
     * Returns the indexes
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * Deletes all the indexes
     */
    public function resetIndexes()
    {
        $this->indexes = array();
    }

    /**
     * Sets the indexes
     * @param $ind
     */
    public function setIndexes($ind)
    {
        $this->indexes = $ind;
    }
}
