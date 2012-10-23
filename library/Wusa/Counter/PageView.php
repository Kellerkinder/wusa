<?php
namespace Wusa\Counter;
use Wusa;
/**
 * Counting a Pageview
 * @author lukas.plattner
 *
 */
class PageView extends Counter
{
    /**
     * @see Wusa\Counter::data
     * @var array
     */
    protected $data = array(
                'uc' => '',
                'pageId' => 0,
                'counterId' => 1,
                'uzeitpunkt' => NULL,
                'szeitpunkt' => 0,
                'referer' => NULL,
                'viewport' => NULL,
                'visitsession' => '',
                );
    /**
     * @see Wusa\Counter::mapping
     * @var array
     */
    protected $mapping = array(
            '_wuut'	=>'uzeitpunkt',
            '_wudr'	=>'referer',
            '_wuvp'	=>'viewport',
            '_wuvid'=>'visitsession',
            );
    
    protected $table = 'cp_pageview';
    public function __construct()
    {
        $this->table= Config::getInstance()->counter->db->prefix.'pageview';
        $this->data['uc'] = $this->getUniqeClient();
        $this->data['pageId'] = $this->getPageId();
        $this->data['szeitpunkt'] = date('Y-m-d H:i:s');
    }
    
    protected function refactorDataForSave($data)
    {
        $data['uzeitpunkt'] = date('Y-m-d H:i:s',$data['uzeitpunkt']/1000);
        $data['visitsession'] = explode('-', $data['visitsession']);
        $data['visitsession'] = $data['visitsession'][2].'-'.$data['visitsession'][3];
        return $data;
    }
}
?>