<?php
require_once '../init.php';
/**
 *
 * @author lukas
 */
include 'tabledefinition.php';
echo "<pre>";

$dbCp = new Wusa\Db(Wusa\Db::CONNECTION_TYPE_MASTER,$config->counter->db->connection);
$metadata = new Wusa\Db\Metadata\Metadata($dbCp->getAdapter());
foreach($tables as $table)
{
    $metadata->createTable($table);
    echo PHP_EOL;
}