<?php

class Report_Model_user extends Zeed_Db_Model
{
    /**
     * @var string The table name.
     */
    protected $_name = 'user_day';

    /**
     * @var integer Primary key.
     */
    protected $_primary = 'id';

    /**
     * @var string Table prefix.
     */
    protected $_prefix = 'report_';

    public function fetchByWhere($where = null, $order = null, $perpage = null, $offset = null, $cols = '*')
    {
    	$table = $this->getTable();
    	if(isset($_GET['type'])&&$_GET['type']){
    		$table = 'report_user_'.$_GET['type'];
    	}
    	$select = $this->getAdapter()->select()->from($table);
    	if ($order !== null) {
    		$select->order($order);
    	}
    	if ($perpage !== null || $offset !== null) {
    		$select->limit($perpage, $offset);
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
    public function getCount($where = null)
    {
    	$table = $this->getTable();
    	if(isset($_GET['type'])&&$_GET['type']){
    		$table = 'report_user_'.$_GET['type'];
    	}
    	$select = $this->getAdapter()->select()->from($table, array("count_num" => "count(*)"));
    	if (is_string($where)) {
    		$select->where($where);
    	} elseif (is_array($where) && count($where)) {
    		/**
    		 * 数组, 支持两种形式.
    		 */
    		foreach ($where as $key => $val) {
    			if (preg_match("/^[0-9]/", $key)) {
    				$select->where($val);
    			} else {
    				$select->where($key . '= ?', $val);
    			}
    		}
    	}
    	$row = $select->query()->fetch();
    	return $row ? $row["count_num"] : 0;
    }
    
    public static function instance()
    {
        return parent::_instance(__CLASS__);
    }
    
}

// End ^ LF ^ encoding

