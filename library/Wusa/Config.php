<?php
namespace Wusa;
use Zend\Log;
use Zend\Log\Writer;
use Zend;
/**
 * Holds the Config
 * @author Lukas Plattner
 */
class Config
{
    /**
     * Instance for singleton
     * @var Config
     */
    protected static $instance = null;
    /**
     * Array der Configurationsdaten
     * @var \Zend\Config\Config
     */
    protected $configdata = array();
    /**
     * Returns Instance of Wusa_Config
     * @return Config
     */
    public static function getInstance()
    {
        if(self::$instance === null)
        {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /**
     * @var \Zend\Log\Logger
     */
    protected $logger = null;

    /**
     * Constructor of Config
     * autoloads the global config
     */
    protected function __construct()
    {
        $this->configdata = new \Zend\Config\Config(array());
        $this->loadConfigFile('global.ini');
        Db::addConfdir($this->configdata->system->db->configdir->toArray());
    }
    /**
     * Loads a Configfile into internal Data
     * @param $file
     * @return bool
     */
    public function loadConfigFile($file)
    {
        $filepath = CONFIG_PATH.$file;
        try{
            if(!file_exists($filepath))
            {
                $this->getLogger()->err(__METHOD__.': Could not Load '.var_export($file,true).' (file not found)');
                return false;
            }
            $reader = new \Zend\Config\Reader\Ini($filepath);
            $this->configdata->merge(new \Zend\Config\Config($reader->fromFile($filepath)));
            return true;
        }
        catch(Exception $e)
        {
            $this->doLog(__METHOD__.': Could not Load '.var_export($file,true).' (Exception '.$e->getMessage().')',\Zend\Log\Logger::ERR);
            return false;
        }
    }

    /**
     * Returns Configparam
     * @param $param
     * @return mixed
     */
    public function __get($param)
    {
        return $this->get($param);
    }
    public function get($param,$default=null)
    {
        return $this->configdata->get($param,$default);
        }

    /**
     * Returns the Systemlogger
     * @return \Zend\Log\Logger
     */
    public function getLogger()
    {
        if($this->logger === null)
        {
            //Logger is Defined in the Config
            $loggerclass = '\\'.$this->configdata->system->logger->class;
            echo $loggerclass;
            $r = new \ReflectionClass($loggerclass);
            $writer = $r->newInstanceArgs((array)$this->configdata->system->logger->options->toArray());

            //$writer =  new \Zend\Log\Writer\Stream('log.log');
            $this->logger =  new \Zend\Log\Logger();
            $this->logger->addWriter($writer);
        }
        return $this->logger;
    }
    /**
     * Logs a Message
     * @param $message
     * @param $prio
     */
    public static function doLog($message,$prio = \Zend\Log\Logger::ERR)
    {
        self::getInstance()->getLogger()->log($prio,$message);
    }
}
