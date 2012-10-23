<?php
require_once 'init.php';

$db = new Wusa\Db(Wusa\Db::CONNECTION_TYPE_SLAVE,'single');
$sql = $db->getSql();
//$sql = $db->createStatement('show tables');
$select = $sql->select('stamm_counter');

$results = $db->query($select);
//$selectString = $sql->getSqlStringForSqlObject($select);
//$results = $db->query($selectString, $db::QUERY_MODE_EXECUTE);

var_dump($results);
echo "<br>\n<br>\n";
echo $results->count();
foreach ($results as $row) {
    echo $row->accountId . "<br>\n";
}


//var_dump($db->query('select * from stamm_account'));