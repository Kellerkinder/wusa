<?php
namespace Wusa\Db\Connect;
use Wusa\Db;
/**
 * @author Lukas Plattner
 */
class Single implements InterfaceConnect
{
    public function connect($config, $type, $serverName)
    {
        $params = array();

        $globalconf = \Wusa\Config::getInstance()->db;

        $params['driverNamespace'] = $config->get('driverNamespace',$globalconf->get('driverNamespace','\\Zend\\Db\\Adapter\\Driver'));
        $params['driver'] = $config->get('driver',$globalconf->get('driver','Mysqli'));
        $params['host'] = $config->get('host',$globalconf->get('host','localhost'));
        $params['port'] = $config->get('port',$globalconf->get('port','3306'));
        $params['username'] = $config->get('username',$globalconf->get('username',''));
        $params['password'] = $config->get('password',$globalconf->get('password',''));
        $params['dbname'] = $config->get('dbname',$globalconf->get('dbname','wusa'));
        $params['characterset'] = $config->get('characterset',$globalconf->get('characterset','utf8'));

        /*
		if (Zend_Registry::isRegistered('activateDbProfiling') && Zend_Registry::get('activateDbProfiling') === true) {
			$params['profiler'] = true;
		}*/

        try {
            $db = new \Zend\Db\Adapter\Adapter($params);
            //$db->setFetchMode(\Zend\Db::FETCH_OBJ);
            //$db->getConnection();
            $db->driver->getConnection();
            $db->driver->createStatement();
            return $db;
        } catch (Exception $ex) {
            Config::doLog(__METHOD__.': Exception '.$ex->getMessage(),\Zend\Log\Logger::ERR);
            return null;
        }

    }
}
