<?php
require_once '../init.php';
/**
 *
 * @author lukas
 */
include 'tabledefinition.php';
echo "<pre>";

$dbCp = new Wusa\Db(Wusa\Db::CONNECTION_TYPE_MASTER,$config->master->db->connection);
$metadata = new Wusa\Db\Metadata\Metadata($dbCp->getAdapter());

foreach($triggers as $trigger)
{
    //$trigger = new Wusa\Db\Metadata\Object\TriggerObject();
    $metadata->createTrigger($trigger);
}
/*foreach($tables as $table)
{
    var_dump($metadata->createTable($table));
    echo PHP_EOL;
}*/