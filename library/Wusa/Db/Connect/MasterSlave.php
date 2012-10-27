<?php
namespace Wusa\Db\Connect;
use \Wusa\Db;
/**
 * @author Lukas Plattner
 */
class MasterSlave implements InterfaceConnect
{
    public function connect(\Zend\Config\Config $config, $type)
    {
        $conn = $config->get('connection',array());
        if(!$conn) throw new \ConnectException('Either Master nor Slaveconnection defined');
        $connFile = $conn->get('master','');
        if(!$connFile) throw new \ConnectException('No Masterconnection defined');
        if($type === Db::CONNECTION_TYPE_SLAVE)
        {
            $connFile = $conn->get('slave',$connFile);
        }
        //return \Wusa\Db\Connect::connect($connFile,$type);
        return Db::factory($type, $connFile);
    }
}
