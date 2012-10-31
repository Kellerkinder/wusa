<?php
namespace Wusa\Db\Metadata;
use Zend\Db\Adapter\Adapter;
/**
 *
 * @author lukas
 */
class Metadata extends \Zend\Db\Metadata\Metadata
{


    /**
     * Create source from adapter
     *
     * @param  Adapter $adapter
     * @return \Zend\Db\Metadata\Source\AbstractSource
     */
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        switch ($adapter->getPlatform()->getName()) {
            case 'MySQL':
                return new Source\MysqlMetadata($adapter);
            /*case 'SQLServer':
                return new Source\SqlServerMetadata($adapter);
            case 'SQLite':
                return new Source\SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new Source\PostgresqlMetadata($adapter);*/
        }

        throw new \Exception('cannot create source from adapter');
    }
}