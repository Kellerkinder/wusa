<?php
namespace Wusa\Db\Connect;
use Wusa\Db;
/**
 * Connect randomly to any of the defined servers
 * @author Lukas Plattner
 */
class Random extends Single implements InterfaceConnect
{
    public function connect(\Zend\Config\Config $config, $type)
    {
        $try = 0;
        $servers= $config->get('server',array());
        $serverkeys = array_flip(array_keys($servers->toArray()));

        if(count($servers) < 1) throw new \ConnectException('No server defined to connect');
        do{
            // Try to connect to random server
            if($try == 0 && $config->get('default') && array_key_exists($config->get('default'),$serverkeys))
            {
                $randomServer = $config->get('default');
            }
            else
            {
                $randomServer = array_rand($serverkeys);
            }
            $db = $this->connectToServerByConfig($config,$servers->$randomServer);
            //$db = self::_connect($servers[$randomServer], $type);
        } while(++$try < $config->get('maxRetries',1) && $db === null);
        return $db;
    }
}
