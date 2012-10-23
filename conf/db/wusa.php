<?php
$config = array();
$config['driver'] = "Mysqli";
$config['prefix'] = "";

$config['global']['dbname'] = 'wusa';
$config['global']['username'] = 'wusa';
$config['global']['password'] = '';
$config['global']['characterset'] = 'utf8';

/**
 * Master DB Virtuelle IP-Adresse
 */
$config['master']['default'] = "master0";

/**
 * Slave DBs
 * vorerst nur localhost, 10.232.190.41 und 10.232.190.51
 */
$config['slave']['default'] = 'slave1';


$config['master']['master0']['host'] = "127.0.0.1";
$config['master']['master0']['port'] = "3306";
$config['master']['master0']['dbname'] = $config['global']['dbname'];
$config['master']['master0']['username'] = $config['global']['username'];
$config['master']['master0']['password'] = $config['global']['password'];
$config['master']['master0']['characterset'] = $config['global']['characterset'];


$config['slave']['slave1']['host'] = "127.0.0.1";
$config['slave']['slave1']['port'] = "3306";
$config['slave']['slave1']['dbname'] = $config['global']['dbname'];
$config['slave']['slave1']['username'] = $config['global']['username'];
$config['slave']['slave1']['password'] = $config['global']['password'];
$config['slave']['slave1']['characterset'] = $config['global']['characterset'];

return $config;