<?php/** * Zeed Platform Project * Based on Zeed Framework & Zend Framework. *  * LICENSE * http://www.zeed.com.cn/license/ *  * @category   Zeed * @package    Zeed_ChangeMe * @subpackage ChangeMe * @copyright  Copyright (c) 2010 Zeed Technologies PRC Inc. (http://www.zeed.com.cn) * @author     Zeed Team (http://blog.zeed.com.cn) * @since      2011-3-21 * @version    SVN: $Id$ */class IndexController extends PageAbstract{    /**     * 单页频道首页     */    public function index()    {        $data['page'] = Page_Model_Listing::instance()->getPage(1, 'index');        $this->setData('data', $data);        $this->addResult(self::RS_SUCCESS, 'php', 'index.index');        return parent::multipleResult(self::RS_SUCCESS);    }    
    /**
     * 单页频道 - 关于我们 - 企业文化
     */
    public function culture()
    {
        $data['page'] = Page_Model_Listing::instance()->getPage(1, 'culture');
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'index.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }    
    /**
     * 单页频道 - 关于我们 - 大事记
     */
    public function memorabilia()
    {
        $data['page'] = Page_Model_Listing::instance()->getPage(1, 'memorabilia');
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'index.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }    
    /**
     * 单页频道 - 关于我们 - 企业荣誉
     */
    public function honor()
    {
        $data['page'] = Page_Model_Listing::instance()->getPage(1, 'honor');
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'index.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }
}// End ^ Native EOL ^ UTF-8
