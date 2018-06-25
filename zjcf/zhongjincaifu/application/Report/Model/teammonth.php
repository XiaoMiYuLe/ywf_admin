<?php

class Report_Model_teammonth extends Zeed_Db_Model
{
    /**
     * @var string The table name.
     */
    protected $_name = 'tuser_month';
    /**
     * @var integer Primary key.
     */
    protected $_primary = 'id';
    /**
     * @var string Table prefix.
     */
    protected $_prefix = 'report_';

    public function fetchByteam($where = null, $order = null, $group=null,$perpage = null, $offset = null, $cols = '*')
    {
    	$table = $this->getTable();
    	
    	$select = $this->getAdapter()->select()->from($table,$cols);
    	$select->join('report_organ_list',"report_organ_list.organ_code = report_tuser_month.organ_code",'organ_name');
    	$select->join('report_team_list',"report_team_list.team_code = report_tuser_month.team_code",array('team_name'));
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

