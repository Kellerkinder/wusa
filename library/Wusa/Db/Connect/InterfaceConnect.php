<?php
namespace Wusa\Db\Connect;
/**
 * @author Lukas Plattner
 */
interface InterfaceConnect
{
    public function connect(\Zend\Config\Config $config, $type);
}
