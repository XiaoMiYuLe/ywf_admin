<?php

class Report_Model_order extends Zeed_Db_Model
{
    /**
     * @var string The table name.
     */
    protected $_name = 'order';

    /**
     * @var integer Primary key.
     */
    protected $_primary = 'id';

    /**
     * @var string Table prefix.
     */
    protected $_prefix = 'report_';

    public function fetchByorder($where = null, $order = null, $group=null,$perpage = null, $offset = null, $cols = '*')
    {
    	$table = $this->getTable();
    	
    	$select = $this->getAdapter()->select()->from($table,$cols);
    	if ($order !== null) {
    		$select->order($order);
    	}
    	if ($group !== null) {
    		$select->group($group);
    	}
    	if ($perpage !== null || $offset !== null) {
    		$select->limit($perpage, $offset);
    	}
    	
    	$row = $select->where($where)->query()->fetchAll();
    	return $row ? $row : null;
    }
    
    public function fetchByorderwhere ($where = null, $order = null, $group=null,$cols = '*')
    {
    	$table = $this->getTable();
    	 
    	$select = $this->getAdapter()->select()->from($table,$cols);
    	if ($order !== null) {
    		$select->order($order);
    	}
    	if ($group !== null) {
    		$select->group($group);
    	}
    	$row = $select->where($where)->query()->fetchAll();
    	return $row ? $row : null;
    }
    
    
    /**
     * 统计表记录条目数
     *
     * @param string|array  $where
     * @return integer
     */
    public function getCount($where = null,$order=null, $group=null)
    {
        $select = $this->getAdapter()->select()->from($this->getTable(), '*');
        $select->where($where);
        if($group){
        	$select->group($group);
        }
        if ($order !== null) {
        	$select->order($order);
        }
        if ($group !== null) {
        	$select->group($group);
        }
        $row = $select->query()->fetchAll();
        return $row ? count($row): 0;
    }
    
    public static function instance()
    {
        return parent::_instance(__CLASS__);
    }
    
}

// End ^ LF ^ encoding

