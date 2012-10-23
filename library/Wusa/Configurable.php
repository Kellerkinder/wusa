<?php
namespace Wusa;
/**
 *
 * @author Lukas Plattner
 */
class Configurable
{
    /**
     * Folders where the Config could be found
     * @var array
     */
    private static $_confDirs = array();

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
    public static function getConfig($config = NULL)
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

}
