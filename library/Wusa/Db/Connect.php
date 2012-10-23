<?php
namespace Wusa\Db;
use Wusa;
/**
 * @author Lukas Plattner
 */
class Connect
{
    /**
     * Array of already used connectors
     * @var array
     */
    protected static $_connectors = array();
    public static function connect($config, $type, $serverName)
    {
        $connectionType = '\\Wusa\\Db\\Connect\\'.$config->get('connectionType','Single');
        if(!array_key_exists($connectionType, self::$_connectors))
        {
            if(!class_exists($connectionType))
            {
                \Wusa\Config::doLog(__METHOD__.'Unknown connectiontype: '.$connectionType);
                throw new \Exception('Unknown connectiontype: '.$connectionType);
            }
            self::$_connectors[$connectionType] = new $connectionType();
        }
        return self::$_connectors[$connectionType]->connect($config, $type, $serverName);
    }
}
