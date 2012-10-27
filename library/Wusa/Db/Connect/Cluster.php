<?php
namespace Wusa\Db\Connect;
use Zend\Config\Config;
use Wusa\Cache;
/**
 * @author Lukas Plattner
 */
class Cluster extends Single implements InterfaceConnect
{
    /**
     * Cache where the Statusinformation is stored
     * @var \Zend\Cache\Storage\StorageInterface
     */
    protected $cache = null;
    /**
     * Config of the complete Connection
     * @var \Zend\Config\Config
     */
    protected $adapterconfig = null;
    /**
     * Status of the Clusternodes
     * @var array
     */
    private $ndb_status = array();
    /**
     * Global Configuration
     * @var \Zend\Config\Config
     */
    private $globalconf = null;

    public function connect(\Zend\Config\Config $config, $type)
    {
        $this->adapterconfig = $config;
        $this->globalconf = \Wusa\Config::getInstance()->system->db;
        $servers = $config->get('server',array())->toArray();
        if(count($servers)<2) throw new \ConnectException('Not enough Server defined');

        $cacheName = $config->get('connectionCache','');
        if(!$cacheName) throw new \ConnectException('No Cache defined');

        $this->cache = Cache::factory($cacheName);

        $cacheKey = Cache::sanitizeId('cluster_status_' . implode(array_keys($servers))); //Generate Cachekey

        $success = false;

        $this->ndb_status = $this->cache->getItem($cacheKey,$success);
        //$ndb_status = apc_fetch($cacheKey, $success);

        if( $success === false) // Could not get from Cache, so generate new one
        {
            //Initial setting of the data
            $this->ndb_status = array();
            foreach ($servers as $key => $val)
            {
                $this->ndb_status[$key] = array("try"=>0,"set"=>time());
            }
            $this->cache->setItem($cacheKey, $this->ndb_status);
        }
        $tries = 0;
        $oldNode = '';
        do{
            do{
                $node = array_rand($servers);
            }while ($node == $oldNode);
            $oldNode = $node;
            $db= $this->_connectClusterNode(
                $node,
                new \Zend\Config\Config($servers[$node]),
                $cacheKey);
        }while($db === null && ++$tries < $this->globalconf->connection->maxtry);
        if($db === null)
        {
            throw new Exception("Connection Error auf mysql_cluster | ".mysql_error());
        }
        return $db;
    }
    /**
     * Connects to clusternode, handles updates to statuscache if needed
     *
     * @return Zend_Db_Adapter_Abstract
     */
    private function _connectClusterNode($nodename, $serverconfig, $cacheKey)
    {
        if(	$this->ndb_status[$nodename]["try"] >= $this->globalconf->connection->maxtry &&
            $this->ndb_status[$nodename]["set"]>(time() - $this->globalconf->connection->timeout)
        )
        {
            return null;
        }
        $starttime = time();
        try{
            $db = $this->connectToServerByConfig($serverconfig,$this->adapterconfig);
        }
        catch(\Exception $e)
        {
            $db = null;
        }

        if($db === null)
        {
            //echo "verbindung nicht m�glich <br>\n";
            $this->ndb_status[$nodename]["try"]++;
            $this->ndb_status[$nodename]["set"] = time();
            $this->cache->setItem($cacheKey, $this->ndb_status);
            return null;
        }
        else
        {
            if(microtime() > ($starttime+$this->globalconf->connection->timeout))
            {
                $this->ndb_status[$nodename]["try"]++;
                $this->ndb_status[$nodename]["set"] = time();
                $this->cache->setItem($cacheKey, $this->ndb_status);
            }
            elseif($this->ndb_status[$nodename]["try"] != 0) //nur zur�cksetzen wenn nicht eh schon alles passt
            {
                $this->ndb_status[$nodename]["try"] = 0;
                $this->cache->setItem($cacheKey, $this->ndb_status);
            }
        }
        return $db;
    }


}
