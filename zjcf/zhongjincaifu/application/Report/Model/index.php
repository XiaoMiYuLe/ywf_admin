<?php

class Report_Model_index extends Zeed_Db_Model
{
    /**
     * @var string The table name.
     */
    protected $_name = 'goods_day';

    /**
     * @var integer Primary key.
     */
    protected $_primary = 'id';

    /**
     * @var string Table prefix.
     */
    protected $_prefix = 'report_';

    public function fetchByWhere($where = null, $order = null, $perpage = null, $offset = null,$cols = '*')
    {
    	$table = $this->getTable();
    	if(isset($_GET['type'])&&$_GET['type']){
    		$table = 'report_goods_'.$_GET['type'];
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
    
    public function getcolletday($day,$type)
    {
    	$table = $this->getTable();
    	$col = "goods_id,sum(day_money) allmoney , sum(day_num) allnum";
    	if(isset($_GET['type'])&&$_GET['type']){
    		$table = 'report_goods_'.$_GET['type'];
    		$col = "goods_id,sum(".$_GET['type']."_money) allmoney , sum(".$_GET['type']."_num) allnum";
    	}
    	$select = $this->getAdapter()->select()->from($table,$col);
    	$where = " date <= "."'{$day}'";
    	$select->group('goods_id');
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
    		$table = 'report_goods_'.$_GET['type'];
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
    
    
    public function UpdateReportGoods()
    {
    	$goods_id = array();
    	$reportgoodsid = array(); // 报表商品
    	$reportnewsid = array(); //  新增商品
    	$buy_num =array(); // 购买数量
    	$goods_name = array(); //产品名称
    	//获取所有商品id
    	$select = $this->getAdapter()->select()->from('goods_list');
    	$where = 'is_del = 0 ';
    	$row = $select->where($where)->query()->fetchAll();
    	foreach ($row as $k=>$v){
    		$goods_id[] = $v['goods_id'];
    		$buy_num[$v['goods_id']] = $v['buy_num'];
    		$goods_name[$v['goods_id']] = $v['goods_name'];
    	}
    	//获取商品报表中的商品id
    	$select = $this->getAdapter()->select()->from($this->getTable(),'goods_id');
    	$reportgoods = $select->query()->fetchAll();
    	 
    	//获取商品报表中的已存在的商品和新增商品
    	if($reportgoods){
    		foreach ($reportgoods as $kk=>$kv){
    			if(in_array($kv['goods_id'],$goods_id)){
    				$reportgoodsid[] = $kv['goods_id'];
    			}else{
    				$reportnewsid[] = $kv['goods_id'];
    			}
    		}
    	}else{
    		$reportnewsid = $goods_id ;
    	}
    	// 如果是新商品 添加到商品报表中 并更新信息
    	if($reportnewsid){
    		foreach ($reportnewsid as $rv){
    			// 插入数据
    			$select = $this->getAdapter()->select()->from('bts_order','SUM(bts_order.real_money) summoney');
    			$where = 'goods_id = '.$rv;
    			$data['day_money'] = $data['all_money'] = $select->where($where)->query()->fetchColumn();
    			$data['all_num'] = $data['day_num'] = $buy_num[$rv];
    			$data['last_time'] = date('y-m-d H:i:s',time());
    			$data['goods_id'] = $rv;
    			$data['goods_name'] = $goods_name[$rv];
    			$this->insert($data);
    		}
    	}
    	if($reportgoodsid){
    		foreach ($reportgoodsid as $rv){
    			// 查询报表中原有数据
    			$select = $this->getAdapter()->select()->from($this->getTable());
    			$where = 'goods_id ='.$rv;
    			$report_good = $select->where($where)->query()->fetch();
    			// 更新数据
    			$select = $this->getAdapter()->select()->from('bts_order','SUM(bts_order.real_money) summoney');
    			$where = 'goods_id = '.$rv;
    			$data['all_money '] =  $select->where($where)->query()->fetchColumn();
    			$data['day_money'] =$data['all_money '] - $report_good['all_money'];
    			$data['all_num'] = $buy_num[$rv];
    			$data['day_num'] = $buy_num[$rv] - $report_good['all_num'];
    			$data['last_time'] = date('y-m-d H:i:s',time());
    			$this->update($data, "goods_id='{$rv}'");
    		}
    	}
    }
    
    public static function instance()
    {
        return parent::_instance(__CLASS__);
    }
    
}

// End ^ LF ^ encoding

