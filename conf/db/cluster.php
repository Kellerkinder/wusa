<?php
$config = array();
$config['connectionType'] = 'Cluster';
$config['connectionCache'] = 'default';
//$config['default'] = ''; //Default case try to connect that server

//$config['driver'] = 'Mysqli';
//$config['prefix'] = '';

$config['port'] = "3306";
$config['dbname'] = 'wusa';
$config['username'] = 'wusa';
$config['password'] = '';
//$config['characterset'] = 'utf8';

$config['server']['server0']['host'] = '10.123.0.2';
//$config['server']['server0']['port'] = '3306';
//$config['server']['server0']['dbname'] = 'wusa';
//$config['server']['server0']['username'] = 'wusa';
//$config['server']['server0']['password'] = '';
//$config['server']['server0']['characterset'] = 'utf8';

$config['server']['server1']['host'] = '127.0.0.1';
//$config['server']['server1']['port'] = '3306';
//$config['server']['server1']['dbname'] = 'wusa';
//$config['server']['server1']['username'] = 'wusa';
//$config['server']['server1']['password'] = '';
//$config['server']['server1']['characterset'] = 'utf8';


return $config;