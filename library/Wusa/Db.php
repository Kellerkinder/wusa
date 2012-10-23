<?php
namespace Wusa;
use Zend\Db\Adapter;

class Db extends Configurable
{
    /**
     * Possible ways of handling new connections
     */
    const RETURN_ONLY    = 1;
	const RETURN_AND_SET = 2;
    /**
     * Connectionstypes
     */
    const CONNECTION_TYPE_SLAVE = 'slave';
    const CONNECTION_TYPE_MASTER = 'master';
    /**
     * Saves the opened Connections
     * @var array
     */
    protected static $_connections = array();
    /**
     * Holds the ID of the active connection
     * @var null|string
     */
    protected static $_activeConnection = null;
	/**
	 * Gets the database connection
	 * 
	 * @param string $type Can be master or slave
	 * @param string $config If not passed the default from Config will be used
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public static function factory($type = self::CONNECTION_TYPE_SLAVE, $config = null, $act = self::RETURN_AND_SET)
	{
        if(!$config)
        {
            $config = Config::getInstance()->system->db->connection->default;
        }
        $key = $type.'_'.$config;

        $config = self::getConfig($config);
        if(!array_key_exists($key,self::$_connections))
        {
            self::$_connections[$key] = Db\Connect::connect($config,$type,'');
        }
        if($act === self::RETURN_AND_SET)
        {
            self::$_activeConnection = $key;
        }
        return self::$_connections[$key];
	}
    /**
     * Holds the Active DB Connection
     * @var null|\Zend\Db\Adapter\Adapter
     */
    protected $connection = null;
    /**
     * name of the Active connection
     * @var null|string
     */
    protected $config = null;
    /**
     * Type of the Connection
     * @var string
     */
    protected $type = self::CONNECTION_TYPE_SLAVE;
    public function __construct($type = self::CONNECTION_TYPE_SLAVE, $config = null, $act = self::RETURN_AND_SET)
    {
        $this->config = $config;
        $this->type = $type;
        $this->connection = self::factory($type,$config,$act);
    }

    /**
     * returns Sql Class from active connection
     * @return \Zend\Db\Sql\Sql
     */
    public function getSql()
    {
        $sql = new \Zend\Db\Sql\Sql($this->connection);
        return $sql;
    }
    /**
     * @param \Zend\Db\Sql\AbstractSql $sql
     * @param string $modeOrOptions
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function query(\Zend\Db\Sql\AbstractSql $sql,$modeOrOptions = \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)
    {
        $string = $this->getSql()->getSqlStringForSqlObject($sql);
        return $this->connection->query($string,$modeOrOptions);
    }

    /**
     * return the Adapter
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getAdapter()
    {
        return $this->connection;
    }

    public function insert($table, $data)
    {
        $sql = $this->getSql();
        $insert = $sql->insert($table);
        $insert->values($data);
        return $this->query($insert);
    }
}
