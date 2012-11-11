<?php
/**
 *
 * @author Lukas Plattner
 */
require_once('../init.php');
$config = Wusa\Config::getInstance();

/*
 * Collect Replacements for SQL Files
 */
$replacement_search = array();
$replacement_replace = array();

//Prefix for CP Tables
$replacement_search[] = '%TABLE_PREFIX_CP%';
$replacement_replace[] = $config->counter->db->prefix;
//Prefix for Mastertables
$replacement_search[] = '%TABLE_PREFIX_MASTER%';
$replacement_replace[] = $config->master->db->prefix;
//Projectname
$replacement_search[] = '%PROJECTNAME%';
$replacement_replace[] = constant('PROJECTNAME');


$sqlCp = file_get_contents('sql/init_cp.sql');
$sqlMaster = file_get_contents('sql/init_master.sql');

$sqlCp = str_replace($replacement_search,$replacement_replace,$sqlCp);
$sqlMaster = str_replace($replacement_search,$replacement_replace,$sqlMaster);

$dbCp = new Wusa\Db(Wusa\Db::CONNECTION_TYPE_MASTER,$config->counter->db->connection);
//$dbMaster = new Wusa\Db(Wusa\Db::CONNECTION_TYPE_MASTER,$config->master->db->connection);

$stmt = $dbCp->getSql();
use Zend\Db\Metadata\Object\TableObject;
use Zend\Db\Metadata\Object\ColumnObject;
$table = new \Wusa\Db\Metadata\Object\TableObject('pageview');
$cols =array();
$col = new \Wusa\Db\Metadata\Object\ColumnObject('uc','pageview');
$col->setDataType('char');
$col->getCharacterMaximumLength(32);
$col->setIsNullable(false);
$cols[] = $col;

$table->setColumns($cols);

//echo "<h1>SQLCP:</h1>";
echo '<pre>';
$metadata = new Wusa\Db\Metadata\Metadata($dbCp->getAdapter());

// get the table names
$tableNames = $metadata->getTableNames();
//echo "Trigger: ".PHP_EOL;
//var_dump($metadata->getTriggers());

$colVars = array(
    'OrdinalPosition',
    'ColumnDefault',
    'IsNullable',
    'DataType',
    'CharacterMaximumLength',
    'CharacterOctetLength' ,
    'NumericPrecision',
    'NumericScale',
    'NumericUnsigned',
    'Comment',
);

$constraintVars = array(
//    'Name',
 //   'TableName',
    'SchemaName',
    'Type',
    'Columns',
    'ReferencedTableSchema' ,
    'ReferencedTableName',
//    'ReferencedColumns',
    'MatchOption',
    'UpdateRule',
    'DeleteRule',
    'CheckClause'
);

$indexVars = array(
    'Unique',
    'Type',
    'Columns',
    'Comment'
);

echo htmlspecialchars('<?php').PHP_EOL.PHP_EOL;
echo '//Prefix for CP Tables
$prefixCp = $config->counter->db->prefix;
//Prefix for Mastertables
$prefixMaster = $config->master->db->prefix;
'.PHP_EOL.PHP_EOL;

echo '$tables = array();'.PHP_EOL;
foreach ($tableNames as $tableName) {
    $table = $metadata->getTable($tableName);
    $printTableName = '"'.str_replace(array('cp_','master_'),array('{$prefixCp}','{$prefixMaster}'),$tableName).'"';
    echo "\$table = new \Wusa\Db\Metadata\Object\TableObject($printTableName);\n";
    echo "\$table->setComment('".$table->getComment()."');\n";
//   echo 'In Table ' . $tableName . PHP_EOL;

    echo '$cols = array(); ' . PHP_EOL;
    foreach ($table->getColumns() as $column) {
        echo '$col = new \Wusa\Db\Metadata\Object\ColumnObject(\''.$column->getName().'\','.$printTableName.');'. PHP_EOL;
        foreach($colVars as $colVar)
        {
            echo '$col->set'.$colVar.'('.var_export(call_user_func(array($column,'get'.$colVar)),true).');'.PHP_EOL;
        }
        echo '$col->setTableName('.$printTableName.');'.PHP_EOL;
        if($column->getErrata('permitted_values'))
        {

            echo '$col->setErrata(\'permitted_values\','.var_export($column->getErrata('permitted_values'),true).');'.PHP_EOL;
        }
        echo '';
        echo '$cols[] = $col;'.PHP_EOL;
    }
    echo '$table->setColumns($cols);'.PHP_EOL;
    echo '$constraints = array();'.PHP_EOL;
    foreach($table->getConstraints() as $constraint)
    {
        echo '$const = new \Zend\Db\Metadata\Object\ConstraintObject('.var_export($constraint->getName(),true).','.$printTableName.');'.PHP_EOL;
        foreach($constraintVars as $cVar)
        {
            echo '$const->set'.$cVar.'('.var_export(call_user_func(array($constraint,'get'.$cVar)),true).');'.PHP_EOL;
        }
        if(is_array($constraint->getReferencedColumns()))
        {
            $cVar = 'ReferencedColumns';
            echo '$const->set'.$cVar.'('.var_export(call_user_func(array($constraint,'get'.$cVar)),true).');'.PHP_EOL;
        }
        echo '$constraints[] = $const;'.PHP_EOL;
    }
    echo '$table->setConstraints($constraints);'.PHP_EOL;

    echo '$indexes = array();'.PHP_EOL;
    foreach($table->getIndexes() as $index)
    {
        echo '$ind = new \Wusa\Db\Metadata\Object\IndexObject('.var_export($index->getName(),true).','.$printTableName.');'.PHP_EOL;
        foreach($indexVars as $iVar)
        {
            echo '$ind->set'.$iVar.'('.var_export(call_user_func(array($index,'get'.$iVar)),true).');'.PHP_EOL;
        }
        echo '$indexes[] = $ind;'.PHP_EOL;
    }
    echo '$table->setIndexes($indexes);'.PHP_EOL;
    echo '$tables[] = $table;';
    echo PHP_EOL;
    /*echo '    With constraints: ' . PHP_EOL;

    echo "\$metadata->createTable(\$table);\n";

    foreach ($metadata->getConstraints($tableName) as $constraint) {
        echo '        ' . $constraint->getName()
            . ' -> ' . $constraint->getType()
            . PHP_EOL;
        if (!$constraint->hasColumns()) {
            continue;
        }
        echo '            column: ' . implode(', ', $constraint->getColumns());
        if ($constraint->isForeignKey()) {
            $fkCols = array();
            foreach ($constraint->getReferencedColumns() as $refColumn) {
                $fkCols[] = $constraint->getReferencedTableName() . '.' . $refColumn;
            }
            echo ' => ' . implode(', ', $fkCols);
        }
        echo PHP_EOL;

    }

    echo '----' . PHP_EOL;*/
}

echo htmlspecialchars('?>').PHP_EOL;
//var_dump($table);
//echo $sqlCp;
//$dbCp->getDriver()->getConnection()->getResource()->multi_query($sqlCp);
/*echo "</pre>";
echo "<h1>SQLMASTER:</h1><pre>";
//$dbMaster->getDriver()->getConnection()->getResource()->multi_query($sqlCp);
//$dbMaster->query($sqlMaster,Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
echo $sqlMaster;
echo "</pre>";*/
?>