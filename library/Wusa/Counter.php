<?php
namespace Wusa;
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
    
    /**
     * Returns Databaseconnection
     * @return \Zend\Db\Adapter\Abstract
     */
    protected function getDb()
    {
        return Tomato_Db::factory('master','cp');
    }
    /**
     * Static Method to instance the correct class for counting
     */
    public static function count()
    {
        try{
            $function = ucfirst(trim($_REQUEST['cmd']));
            $class = __NAMESPACE__.'\\'.__CLASS__.'\\'.$function;
            if(class_exists($class))
            {
                $cl = new $class();
                $cl->doCount();
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
    }
    
    protected function checkCounterId()
    {
        $aId = $_REQUEST['_wuaid'];
        $aId = explode('-', $aId);
        
        try{
            $account = $this->getAccount($aId[1]);
//             echo "account: ";var_dump($account);echo"<br>\n";
            if($account == false) return false;
            
            $counter = $this->getCounter($aId[2]);
//             echo "counter: ";var_dump($counter);echo"<br>\n";
            if($counter == false) return false;
//             $_REQUEST['_wuhn'] = 'kurier.at';
//             echo $counter->domainregex."<br>\n";
//             echo $_REQUEST['_wuhn']."<br>\n";
//             var_dump(preg_match($counter->domainregex, $_REQUEST['_wuhn']));
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
        $key = Tk_Cache::sanitizeId('account_'.$acc);
        if(!($account = $cache->load($key)))
        {
            $db = $this->getDb();
            $account = $db->select()->from('stamm_account')->where('accountId = ?',$acc)->query()->fetchObject();
            $cache->save($account);
        }
        return $account;
    }
    protected function getCounter($trac)
    {
        $cache = $this->getCache();
        $key = Tk_Cache::sanitizeId('counter_'.$trac);
        if(!($counter = $cache->load($key)))
        {
            $db = $this->getDb();
            $counter = $db->select()->from('stamm_counter')->where('counterId = ?',$trac)->query()->fetchObject();
            $cache->save($counter);
        }
        return $counter;
    }
    
    
    /**
     * @return Zend_Cache
     */
    protected function getCache()
    {
        return Tk_Cache::factory('shop','shop');
    }
    
    protected function getUniqeClient()
    {
        $db = $this->getDb();
        $data = array();
        $data['ip'] = $_SERVER['REMOTE_ADDR'];
        $data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['session'] = explode('-',$_REQUEST['_wucid']);
        $data['session'] = $data['session'][2].'-'.$data['session'][3];
        $data['screenresolution'] = $_REQUEST['_wusr'];
        
        $return = $db->select()->from('cp_uc','uc')->where('uc = ?',new Zend_Db_Expr('md5('.$db->quoteInto('?', $data['session'].$data['screenresolution']).')'))->query();
        if($return->rowCount()>0)
        {
            return $return->fetchColumn();
        }
        else {
            try{
                $data['uc'] = new Zend_Db_Expr('md5('.$db->quoteInto('?', $data['session'].$data['screenresolution']).')');
                $db->insert('cp_uc', $data);
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
        $data = array();
        $data ['url'] = $_REQUEST['_wudp'];
        $data ['domain'] = $_REQUEST['_wuhn'];       
        $return = $db->select()->from('cp_page','pageId')->where('url = ?',$data['url'])->where('domain = ?',$data['domain'])->query();
        
        if($return->rowCount()>0)
        {
            return $return->fetchColumn();
        }
        else {
            $db->insert('cp_page', $data);
            return $db->lastInsertId();
        }
        
    }
}
?>