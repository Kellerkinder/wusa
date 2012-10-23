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

//Init Autoloader
require_once(LIBRARY_PATH.DIRECTORY_SEPARATOR.'autoload.inc.php');

$config = Wusa\Config::getInstance();
