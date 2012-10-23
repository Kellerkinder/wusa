<?php
namespace Wusa\Db\Connect;
/**
 * @author Lukas Plattner
 */
class MasterSlave implements InterfaceConnect
{
    public function connect($config, $type, $serverName)
    {

    }


    protected static function _connectDefaultRandom($config,$type,$appName)
    {
        $servers = $config['db'][$type];
        // First, try to connect the default server
        $db = self::_connect($config, $type, $servers['default']);
        if ($db == null) {
            // Try to connect to random server
            $randomServer = $servers;
            unset($randomServer['default']);
            $randomServer = array_rand($randomServer);

            $db = self::_connect($config, $type, $randomServer);
            if ($db == null) {
                throw new Exception('Cannot connect to both default and random servers');
            }
        }
        return $db;
    }



    protected static function _connectCluster($config,$type,$appName)
    {
        $servers = $config['db'][$type];
        if(isset($servers['default'])) unset($servers['default']);

        $node = array_rand($servers); //Generieren einer Zufallszahl anhan derer ein Verbindungsversuch gemacht wird

        $cachekey = 'cluster_status_' . str_replace('.', '_', $appName); //generieren eines Uniqe Keys je Cluster

        $success = false;
        $ndb_status = apc_fetch($cachekey, $success);

        if( $success === false) // Key konnte nicht aus dem Cache geholt werden
        {
            //Initiales Setzen des ndb_status arrays
            $ndb_status = array();
            foreach ($servers as $key => $val)
            {
                $ndb_status[$key] = array("try"=>0,"set"=>time());
            }
            apc_store($cachekey, $ndb_status);
        }

        $db= self::_connectClusterNode($config,$type,$ndb_status,0,$node,$cachekey);

        if($db === null)
        {
            throw new Exception("Connection Error auf mysql_cluster | ".mysql_error());
        }
        return $db;
    }

    /**
     * Stellt die Verbindung zu einer Node her
     * Bricht nach der Maximalen anzahl der Versuche ab
     * Wenn eine Node nicht reagiert, wird bei der n�chsten Node versucht zu verbinden
     *
     * @return Zend_Db_Adapter_Abstract
     */
    private static function _connectClusterNode(array $config,$type,array $ndb_status, $try,$node,$cachekey)
    {
        if($try>=self::CLUSTERMAXTRY) //Maximale Anzahl der Verbindungsversuche erreicht
        {
            return false;
        }
        //echo "Node: $node<br>\n";

        $servers = $config['db'][$type];
        if(isset($servers['default'])) unset($servers['default']);

        if(	$ndb_status[$node]["try"] >= self::CLUSTERMAXTRYNODE  &&
            $ndb_status[$node]["set"]>(time() - self::CLUSTERINFOTIMEOUT)
        )
        {
            do{
                $nextNode = array_rand($servers);
            }while($nextNode == $node);
            // Mit dieser Node kann nicht verbunden werden weil sie vermutlich nicht reagiert
            return self::_connectClusterNode($config,$type, $ndb_status, ++$try, $nextNode,$cachekey);
        }
        $starttime = microtime();

        try{
            $db = self::_connect($config, $type, $node);

            if($db !== null)
            {
                if(!$db->getConnection())
                {
                    $db = null;
                }

            }
        }
        catch(Exception $e)
        {
            $db = null;
        }

        if($db === null)
        {
            //echo "verbindung nicht m�glich <br>\n";
            $ndb_status[$node]["try"]++;
            $ndb_status[$node]["set"] = time();
            apc_store($cachekey, $ndb_status);
            do{
                $nextNode = array_rand($servers);
            }while($nextNode == $node);
            // Mit dieser Node kann nicht verbunden werden weil sie vermutlich nicht reagiert
            return self::_connectClusterNode($config,$type, $ndb_status, ++$try, $nextNode,$cachekey);
        }
        else
        {
            if(microtime() > ($starttime+self::MAXCONNECTIONTIMEOUT))
            {
                $ndb_status[$node]["try"]++;
                $ndb_status[$node]["set"] = time();
                apc_store($cachekey, $ndb_status);
            }
            elseif($ndb_status[$node]["try"] != 0) //nur zur�cksetzen wenn nicht eh schon alles passt
            {
                $ndb_status[$node]["try"] = 0;
                apc_store($cachekey, $ndb_status);
            }
        }
        return $db;
    }
    protected static function _connectNode($config,$type,$appName)
    {
        $connectionType = isset($config['db'][$type]['conectiontype'])?$config['db'][$type]['conectiontype']:'defaultRandom';

        if(isset($config['db'][$type]['conectiontype'])) unset($config['db'][$type]['conectiontype']);

        $func = '_connect'.ucfirst($connectionType);

        if(!method_exists(__CLASS__, $func))
        {
            throw new Exception('Unknown Connection Type: '.$connectionType);
        }

        return call_user_func_array(array(__CLASS__,$func), array($config,$type,$appName));
    }


}
