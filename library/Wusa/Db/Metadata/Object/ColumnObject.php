<?php
namespace Wusa\Db\Metadata\Object;
/**
 *
 * @author lukas
 */
class ColumnObject extends \Zend\Db\Metadata\Object\ColumnObject
{
    /**
     * @var string
     */
    protected $comment = '';
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

}
