<?php
namespace Wusa;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
/**
 * Counter
 * @author lukas.plattner
 */
abstract class Counter{
    
    /**
     * Defining which data will be saved in Table
     * @var array
     */
    protected $data = array();
    /**
     * Table where the data should be stored
     * @var string
     */
    protected $table = '';
    /**
     * Mapping from post- and getvars to tablefields
     * @var array
     */
    protected $mapping = array();
    private $db = null;
    /**
     * Returns Databaseconnection
     * @return \Wusa\Db
     */
    protected function getDb()
    {
        if(!$this->db)
        {
            $this->db = new Db(Db::CONNECTION_TYPE_MASTER,Config::getInstance()->counter->db->get('connection'));
        }
        return $this->db;
    }
    /**
     * Static Method to instance the correct class for counting
     */
    public static function count()
    {
        try{
            if(!array_key_exists('cmd',$_REQUEST))
            {
                Config::doLog('CP: no cmd defined ',\Zend\Log\Logger::NOTICE);
                return;
            }
            $function = ucfirst(trim($_REQUEST['cmd']));
            $class =__CLASS__.'\\'.$function;
            if(class_exists($class))
            {
                $cl = new $class();
                $cl->doCount();
            }
            else
            {
                Config::doLog('CP: Invalid function '.var_export($function,true),\Zend\Log\Logger::NOTICE);
                return;
            }
        }
        catch(Exception $e) //Wenn was Schief geht abfangen
        {
            Config::doLog('CP: '.$e->getMessage(),\Zend\Log\Logger::ERR);
        }
        
    }
    /**
     * Methode die überschrieben werden kann um die Daten zu überschreiben
     * @param unknown_type $data
     * @return unknown
     */
    protected function refactorDataForSave($data)
    {
        return $data;
    }
    
    
    protected function doCount()
    {
        if(!$this->checkCounterId()) return;
//         echo "Zähle";
        foreach ($this->mapping as $valname => $key)
        {
            if(array_key_exists($valname,$_REQUEST))
            {
                $this->data[$key] = trim($_REQUEST[$valname]);
            }
        }
        
        foreach ($this->data as $key => &$value)
        {
            if(!$value) $value = NULL;
        }
        
        $data = $this->refactorDataForSave($this->data);
        $this->getDb()->insert($this->table, $data);

/*
        $db = $this->getDb();
        $sql = $db->getSql();
        $insert = $sql->insert($this->table);
        $insert->values($data);
        $result = $db->query($insert);*/
    }
    
    protected function checkCounterId()
    {
        $aId = $_REQUEST['_wuaid'];
        $aId = explode('-', $aId);
        
        try{
            $account = $this->getAccount($aId[1]);
            var_dump($account);
            if($account == false) return false;
            
            $counter = $this->getCounter($aId[2]);
            var_dump($counter);
            if($counter == false) return false;
            if(!preg_match($counter->domainregex,$_REQUEST['_wuhn'])) return false; 
            
            return true;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
    
    protected function getAccount($acc)
    {
        $cache = $this->getCache();
        $key = Cache::sanitizeId('account_'.$acc);
        if($cache->hasItem($key))
        {
            $account = $cache->getItem($key);
        }
        else
        {
            $db = $this->getDb();
            $sql = $db->getSql();
            $where = new Where();
            $where->equalTo('accountId',$acc);
            $select = $sql->select('stamm_account')->where($where);

            $return = $db->query($select);
            $account = $return->current();
            $cache->setItem($key,$account);
        }
        return $account;
    }
    protected function getCounter($trac)
    {
        $cache = $this->getCache();
        $key = Cache::sanitizeId('counter_'.$trac);
        if($cache->hasItem($key))
        {
            $counter = $cache->getItem($key);
        }
        else
        {
            $db = $this->getDb();
            $sql = $db->getSql();
            $where = new Where();
            $where->equalTo('counterId',$trac);
            $select = $sql->select('stamm_counter')->where($where);
            $return = $db->query($select);
            $counter = $return->current();
            $cache->setItem($key,$counter);
        }
        return $counter;
    }
    
    
    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    protected function getCache()
    {
        return Cache::factory('default');
    }
    
    protected function getUniqeClient()
    {
        $db = $this->getDb();
        $sql = $db->getSql();
        $data = array();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['session'] = explode('-',$_REQUEST['_wucid']);
        $data['session'] = $data['session'][2].'-'.$data['session'][3];
        $data['screenresolution'] = $_REQUEST['_wusr'];


        $ucExpr = new Expression('md5(?)',$data['session'].$data['screenresolution']);
        $where = new Where();
        $where->equalTo('uc',$ucExpr);
        $select = $sql->select('cp_uc','uc')->where($where);

        $return = $db->query($select);
        if($return->count()>0)
        {
            return current($return->current());
        }
        else {
            try{
                $data['uc'] = $ucExpr;
                $insert = $sql->insert('cp_uc');
                $insert->values($data);
                $db->query($insert);
            }
            catch(Exception $e)
            {
                
            }
            return $this->getUniqeClient();
        }
    }
    
    protected function getPageId()
    {
        $db = $this->getDb();
        $sql = $db->getSql();
        $data = array();
        $data ['url'] = $_REQUEST['_wudp'];
        $data ['domain'] = $_REQUEST['_wuhn'];

        $where = new Where();
        $where->equalTo('url',$data['url']);
        $where->equalTo('domain',$data['domain']);

        $select = $sql->select('cp_page','pageId')->where($where);
        $return = $db->query($select);
        //$return = $db->select()->from('cp_page','pageId')->where('url = ?',$data['url'])
        //    ->where('domain = ?',$data['domain'])->query();
        
        if($return->count()>0)
        {
            return current($return->current());
        }
        else {
            $insert = $sql->insert('cp_page');
            $insert->values($data);
            $result = $db->query($insert);

            return $db->getAdapter()->getDriver()->getLastGeneratedValue();
        }
    }
}
?>