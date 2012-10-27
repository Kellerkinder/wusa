<?php
$config = array();
$config['connectionType'] = 'MasterSlave';

//Just Define the Configfiles for Masterconnection and Slaveconnection
$config['connection']['master'] = 'single';
$config['connection']['slave'] = 'random';

return $config;