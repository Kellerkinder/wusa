<?php

$config = array();
$config['adapter']['name'] = 'filesystem';
$config['adapter']['options']['ttl'] = 70;
$config['adapter']['options']['cache_dir'] = APPLICATION_PATH.DIRECTORY_SEPARATOR.'cache';
$config['adapter']['options']['dir_permission'] = '777';
$config['adapter']['options']['dir_permission'] = '777';
$config['plugins'] = array(
    'exception_handler' => array('throw_exceptions' => false),
    'serializer'=> array(),
);
return $config;
?>