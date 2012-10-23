<?php
namespace Wusa;
use Zend\Db\Adapter;

class Db
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
     * Ordner in denen nach der Config gesucht werden soll
     * @var array
     */
    protected static $_confDirs = array();
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

        $config = self::getDbConfig($config);
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
     * Returns the Configfile from Configname
     * @param $config
     * @return bool|string
     */
    protected static function getConfigfile($config)
    {
        foreach(self::$_confDirs as $confdir)
        {
            $path = $confdir.DIRECTORY_SEPARATOR.$config.'.php';
            if(file_exists($path))
            {
                return $path;
            }
        }
        return false;
    }

    /**
     * Adds a path to the list of configdirs
     * @param $string
     * @return bool
     */
    public static function addConfdir($string)
    {
        if(is_array($string))
        {
            $return = true;
            foreach($string as $str)
            {
                $return = $return && self::addConfdir($str);
            }
            return $return;

        }
        if(is_dir($string))
        {
            self::$_confDirs[] = $string;
            return true;
        }
        return false;
    }

    /**
     * Resets the List of Configdirs
     */
    public static function resetConfdirs()
    {
        self::$_confDirs = array();
    }
	/**
	 * Gets database settings
	 * 
	 * @param string $appName The application name
	 * @return array
	 */
	public static function getDbConfig($config = NULL)
	{
        $configfile = self::getConfigfile($config);

        if(!$configfile){
            Config::doLog(__METHOD__.': Configfile for '.$config.' could not be found', \Zend\Log\Logger::ERR);
            throw new \Exception('Configfile for '.$config.' could not be found');
        }
        $config = include $configfile;
        if (!is_array($config)) {
            Config::doLog(__METHOD__.': The configuration file ' . $configfile . ' does not return array', \Zend\Log\Logger::ERR);
            throw new \Exception('The configuration file ' . $configfile . ' does not return array');
        }
        return new \Zend\Config\Config($config);
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
     * @return mixed
     */
    public function query(\Zend\Db\Sql\AbstractSql $sql,$modeOrOptions = \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE)
    {
        $string = $this->getSql()->getSqlStringForSqlObject($sql);
        return $this->connection->query($string,$modeOrOptions);
    }
}
