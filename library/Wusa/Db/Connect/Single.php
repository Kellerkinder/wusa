<?php
namespace Wusa\Db\Connect;
use Wusa\Db;
/**
 * @author Lukas Plattner
 */
class Single implements InterfaceConnect
{
    public function connect(\Zend\Config\Config $config, $type)
    {
        return $this->connectToServerByConfig($config);
    }
    protected function connectToServerByConfig(\Zend\Config\Config $adapterconfig,\Zend\Config\Config  $serverconfig = null)
    {
        $params = $this->getExplicitServerConnectionParams($adapterconfig,$serverconfig);

        return $this->connectToServerbyParams($params);
    }
    /**
     * Connects to Server by given params, returns null on error,
     * @param array $params
     * @return null|\Zend\Db\Adapter\Adapter
     */
    protected function connectToServerbyParams(array $params)
    {
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

    /**
     * Creates the Connectionparams for one server form Global,Adapter and Serverconfig
     * @param \Zend\Config\Config $adapterconfig
     * @param \Zend\Config\Config $serverconfig
     * @return array
     */
    protected function getExplicitServerConnectionParams(\Zend\Config\Config $adapterconfig,\Zend\Config\Config  $serverconfig = null)
    {
        if(!$serverconfig)
        {
            $serverconfig = new \Zend\Config\Config(array());
        }
        $params = array();

        $globalconf = \Wusa\Config::getInstance()->db;

        $params['driverNamespace'] = $serverconfig->get('driverNamespace',$adapterconfig->get('driverNamespace',$globalconf->get('driverNamespace','\\Zend\\Db\\Adapter\\Driver')));
        $params['driver'] = $serverconfig->get('driver',$adapterconfig->get('driver',$globalconf->get('driver','Mysqli')));
        $params['host'] = $serverconfig->get('host',$adapterconfig->get('host',$globalconf->get('host','localhost')));
        $params['port'] = $serverconfig->get('port',$adapterconfig->get('port',$globalconf->get('port','3306')));
        $params['username'] = $serverconfig->get('username',$adapterconfig->get('username',$globalconf->get('username','')));
        $params['password'] = $serverconfig->get('password',$adapterconfig->get('password',$globalconf->get('password','')));
        $params['dbname'] = $serverconfig->get('dbname',$adapterconfig->get('dbname',$globalconf->get('dbname','wusa')));
        $params['characterset'] = $serverconfig->get('characterset',$adapterconfig->get('characterset',$globalconf->get('characterset','utf8')));

        return $params;
    }
}
