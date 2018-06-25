<?php
/**
 * Firestone_Protocol_C2G_ADLinkCtrl
 * 
 * @package Firestone_Protocol_C2G
 * @since 2010-8-30
 * @author yaojie
 * 通过帐号查询角色名及帐号信息(计费信息)
 */

class Firestone_Protocol_C2G_ADLinkCtrl extends Firestone_Protocol_QMEProtocolSendAbstract
{
    
    const DATA_FORMAT = "a*x";
    
    protected $_data = array('data' => '');
    
    /**
     * @return Firestone_Protocol_C2G_ADLinkCtrl
     */
    public static function instance()
    {
        return parent::_instance();
    }
    
    /**
     * 设置data
     * @param array|string $key 
     * @param integer|string|null $val
     * @return Firestone_Protocol_C2G_ADLinkCtrl
     */
    public function setData($key, $val = null)
    {
        if (is_array($key) && !is_numeric($key)) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }
        } else {

            $this->_data[$key] = $val;
        }
        return $this;
    }
    
    /**
     * 生产data二进制串
     * @return Firestone_Protocol_C2G_ADLinkCtrl
     */
    public function makeDataPack()
    {
    	if (!empty($this->_data['data'])){
            $this->_dataPackString = pack(self::DATA_FORMAT, $this->_data['data']);
    	}
        return $this;
    }
    
    /**
     * 生成最终的二进制串
     */
    public function makeReturnPackString($header, $data)
    {
        $this->setHeader($header)->setData($data);
        $this->makeHeaderPack()->makeDataPack();
        
        if ('' == $this->_headerPackString) 
        {
            throw new Zeed_Exception('headerPackString is empty');
            return;
        }
        
        $this->_returnPackString = $this->_headerPackString . $this->_dataPackString;
        return $this->_returnPackString;
    }

}


// End ^ Native EOL ^ encoding