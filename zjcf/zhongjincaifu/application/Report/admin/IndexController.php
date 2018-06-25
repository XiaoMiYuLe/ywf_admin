<?php

class IndexController extends ReportAdminAbstract
{
    public $perpage = 15;

    public function index()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        $page = (int) $this->input->get('pageIndex');
        $goods_name= $this->input->get('goods_name', null);
        $type= $this->input->get('type', null);
        $day= $this->input->get('ctime', null);
        $page = $page > 0 ? $page + 1 : 1;
        $perpage = $this->input->get('pageSize', $this->perpage);
        $offset = ($page - 1) * $perpage;
        $now= date('Y-m-d',time());
        if($type=='month'){
        	$now= date('Y-m',time());
        }else if($type=='year'){
        	$now= date('Y',time());
        }
        $where = "1 = 1 ";
        if (isset($goods_name) && $goods_name != null) {
            $where .= " and goods_name = "."'{$goods_name}'";
        }
        if (isset($day) && $day != null&&$day) {
        	$where .= " and date = "."'{$day}'";
        }else{
        	$day = $now;
        	$where .= " and date = "."'{$day}'";
        }
        $orderby = 'goods_id';
        $listing = Report_Model_index::instance()->fetchByWhere($where, $orderby, $perpage, $offset);
        
        $count = Report_Model_index::instance()->getcolletday($day,$type);
        $newcount = array();
        if($count){
        	foreach ($count as $ck=>$cv){
        		$goods_id =$cv['goods_id'];
        		unset($cv['goods_id']);
        		$cv['allmoney'] = $cv['allmoney']/10000;
        		$newcount[$goods_id] = $cv;
        	}
        }
        $ssname = $addmoney = $allmoney = $addnum = $allnum ='' ;
        if($listing){
        	foreach($listing as $k=>$v){
        			$listing[$k]['money'] = $listing[$k]['day_money']/10000;
        			$listing[$k]['num'] = $listing[$k]['day_num'];
			       	if($type=='month'){
			        		$listing[$k]['money'] = $listing[$k]['month_money']/10000;
        					$listing[$k]['num'] = $listing[$k]['month_num'];
			        }else if($type=='year'){
			        		$listing[$k]['money'] = $listing[$k]['year_money']/10000;
        					$listing[$k]['num'] = $listing[$k]['year_num'];
			        }
        			if(isset($newcount[$v['goods_id']])){
        				$listing[$k] = array_merge($listing[$k],$newcount[$v['goods_id']]);
        			}else{
        				$listing[$k]['allmoney'] = 0;
        				$listing[$k]['allnum'] = 0;
        			}
        		$ssname .= "'".$listing[$k]['goods_name']."'".",";
        		$addmoney .= $listing[$k]['money'].",";
        		$allmoney .= $listing[$k]['allmoney'].",";
        		$addnum .= $listing[$k]['num'].",";
        		$allnum .= $listing[$k]['allnum'].",";
        	}
        }
        $data['ssname'] = rtrim($ssname,','); 
        $data['addmoney'] = rtrim($addmoney,',');
        $data['allmoney'] = rtrim($allmoney,',');
        $data['addnum'] = rtrim($addnum,',');
        $data['allnum'] = rtrim($allnum,',');
        $data['cdate'] = (string)isset($day)&&$day?$day:$now;
        $data['listing'] = $listing ? $listing : array();
        $data['count'] = Report_Model_index::instance()->getCount($where);
        $data['page'] = $page;
        $data['perpage'] = $perpage;
        $data['order_type'] = $order_type;
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'index.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }
}