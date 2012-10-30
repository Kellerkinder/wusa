<?php
/**
 * Initalising Application
 * @author Lukas Plattner
 */
//Basepath of the Application
define('APPLICATION_PATH',dirname(__FILE__));
//Basepath of the Configuration
define('CONFIG_PATH',APPLICATION_PATH.DIRECTORY_SEPARATOR.'conf');
//Basepath of the Library
define('LIBRARY_PATH',APPLICATION_PATH.DIRECTORY_SEPARATOR.'library');
//Define Projectname
define('PROJECTNAME','WUSA');

//Init Autoloader
require_once(LIBRARY_PATH.DIRECTORY_SEPARATOR.'autoload.inc.php');

$config = Wusa\Config::getInstance();


Wusa\Db::addConfdir($config->system->db->configdir->toArray());
Wusa\Cache::addConfdir($config->system->cache->configdir->toArray());
