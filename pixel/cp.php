<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once('../init.php');
//Autoloader
//Wusa_Counter::count();

// Bild ausgeben :)
Header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAwABAIABALCvn////yH5BAEAAAEALAAAAAADAAEAAAICRFIAOw==');

register_shutdown_function(array('Tk_App_Counter','count'));

?>
