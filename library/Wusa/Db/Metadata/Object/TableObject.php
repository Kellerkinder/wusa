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
}
