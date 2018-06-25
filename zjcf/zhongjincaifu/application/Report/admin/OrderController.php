<?php

class OrderController extends ReportAdminAbstract
{
    public $perpage = 15;

    public function index()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        $page = (int) $this->input->get('pageIndex');
        $type= $this->input->get('type', null);
        $page = $page > 0 ? $page + 1 : 1;
        $min= $this->input->get('min', null);
        $max= $this->input->get('max', null);
        $perpage = $this->input->get('pageSize', $this->perpage);
        $offset = ($page - 1) * $perpage;
        $where = "1 = 1 ";
        $orderby ='oder';
        $group = 'oder';
        $cols = 'id,oder,count(userid) num, FORMAT(SUM(money)/10000,2) summoney';
        if($type=='money'){
        	$orderby =$type;
        	$group = $type ;
        	$cols = 'id,oder,count(userid) num,FORMAT(money/10000,2) summoney';
        	if(isset($min)&&$min){
        		$where .= " and money >= ".$min;
        	}
        	if(isset($max)&&$max){
        		$where .= " and money <= ".$max;
        	}
        }else{
        	if(isset($min)&&$min){
        		$where .= " and oder >= ".$min;
        	}
        	if(isset($max)&&$max){
        		$where .= " and oder <= ".$max;
        	}
        }
       //$listing = Report_Model_order::instance()->fetchByorder($where, $orderby,$group, $perpage, $offset,$cols);
        $carr = Report_Model_order::instance()->fetchByorderwhere($where, $orderby,$group,$cols);
       // $data['count'] = Report_Model_order::instance()->getCount($where,$orderby,$group);
//         if($listing){
//         	$num = 0;
//         	foreach($listing as $k=>$v){
//         		$listing[$k]['id'] = ($page-1)*$perpage+$k+1;
//         	}
//         }
        $ssname = '';
        if($carr){
        	$carr = array_chunk($carr, floor(count($carr)/7)+1);
        	foreach ($carr as $k=>$v){
        		if($type=='money'){
        			unset($carr[$k]);
        			$carr[$k]['id'] = $k+1;
        			$carr[$k]['summoney'] = $v[0]['summoney']."--".$v[count($v)-1]['summoney'];
        			$ssname .= "'(".$carr[$k]['summoney'].")'".',';
        			$carr[$k]['num'] = $carr[$k]['oder'] = 0;
        			foreach ($v as $kv =>$vv){
        				$carr[$k]['num']  += $vv['num'];
        				$carr[$k]['oder']  += $vv['oder'];
        			}
        			$allnum .= $carr[$k]['num'].',';
        			$allmoney .= $carr[$k]['oder'].',';
        			$data['yname']='交易笔数';
        		}else{
        			unset($carr[$k]);
        			$carr[$k]['id'] = $k+1;
        			$carr[$k]['oder'] = $v[0]['oder']."--".$v[count($v)-1]['oder'];
        			$ssname .= "'(".$carr[$k]['oder'].")'".',';
        			$carr[$k]['num'] = $carr[$k]['summoney'] = 0;
        			foreach ($v as $kv =>$vv){
        				$carr[$k]['num']  += $vv['num'];
        				$carr[$k]['summoney']  += $vv['summoney'];
        			}
        			$allnum .= $carr[$k]['num'].',';
        			$allmoney .= $carr[$k]['summoney'].',';
        			$data['yname']='交易金额(万元)';
        		}
        	}
        }
        $data['ssname'] = rtrim($ssname,',');
        $data['allnum'] = rtrim($allnum,',');
        $data['allmoney'] = rtrim($allmoney,',');
        $data['count'] = count($carr);
        $data['page'] = $page;
        $data['perpage'] = $perpage;
        $data['order_type'] = $order_type;
        $data['listing'] = $carr ? $carr : array();
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'order.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }
}