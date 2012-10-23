<?php
/**
 * Initalising Application
 * @author Lukas Plattner
 */
//Basepath of the Application
define('APPLICATION_PATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
//Basepath of the Configuration
define('CONFIG_PATH',APPLICATION_PATH.'conf'.DIRECTORY_SEPARATOR);
//Basepath of the Library
define('LIBRARY_PATH',APPLICATION_PATH.'library'.DIRECTORY_SEPARATOR);

//Init Autoloader
require_once(LIBRARY_PATH.'autoload.inc.php');

$config = Wusa\Config::getInstance();
