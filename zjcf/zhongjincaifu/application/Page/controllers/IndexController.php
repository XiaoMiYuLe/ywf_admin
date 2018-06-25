<?php
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
}