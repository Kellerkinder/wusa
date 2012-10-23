<?php
namespace Wusa\Db\Connect;
/**
 * @author Lukas Plattner
 */
interface InterfaceConnect
{
    public function connect($config, $type, $serverName);
}
