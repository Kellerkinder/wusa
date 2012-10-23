<?php
/**
 * Initialisiert den Autoloader
 */

// Include-Pfad erweitern
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . LIBRARY_PATH);

include LIBRARY_PATH . 'Zend/Loader/AutoloaderFactory.php';
$autoloader = Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
            'Wusa' => LIBRARY_PATH . 'Wusa',
        ),
        'autoregister_zf' => true
    ),
));

/**
 * Zend-Autoloader einbinden
 */
/*require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();

// Autoloader für Tk-Namensraum laden
$autoloader->registerNamespace('Wusa_');

/**
 * Autoloader für Smarty-Klassen 
 */
function autoloaderSmarty($class) {
	// In der Smarty-Klassendatei werden einige Konstanten definiert, die wir auch f�r die
	// anderen Klassen benütigen
	if ($class == 'Smarty' || !defined('SMARTY_SYSPLUGINS_DIR')) {
		include LIBRARY_PATH.'Smarty/Smarty.class.php';
	}
	$_class = strtolower($class);
    if (substr($_class, 0, 16) === 'smarty_internal_' || $_class == 'smarty_security') {
    	include SMARTY_SYSPLUGINS_DIR . $_class . '.php';
    }
}
//$autoloader->pushAutoloader('autoloaderSmarty');
