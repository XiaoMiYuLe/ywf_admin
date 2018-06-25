<?php

class TeamController extends ReportAdminAbstract
{
    public $perpage = 15;

    public function index()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        $page = (int) $this->input->get('pageIndex');
        $name= $this->input->get('name', null);
        $type= $this->input->get('type', null);
        $datetype= $this->input->get('datetype', null);
        $ctime= $this->input->get('ctime', null);
        $page = $page > 0 ? $page + 1 : 1;
        $perpage = $this->input->get('pageSize', $this->perpage);
        $offset = ($page - 1) * $perpage;
        $now= date("Y-m-d",strtotime("-1 day"));
        if(isset($datetype)&&$datetype){
	        if($datetype=='month'){
	        	$now= date('Y-m',time());
	        }else if($datetype=='year'){
	        	$now= date('Y',time());
	        }
        }
        
        if (isset($ctime) && $ctime != null&&$ctime) {
        	$where = " date = "."'{$ctime}'";
        }else{
        	$where =" date ='".$now."'" ;
        }
        
        if (isset($name) && $name != null) {
        	if($type&&$type=='team'){
        		$where .= " and report_team_list.team_code = "."'{$name}'";
        	}elseif($type&&$type=='organ'){
        		$where .= " and report_organ_list.organ_code = "."'{$name}'";
        	}else{
        		$where .= " and user_code = "."'{$name}'";
        	}
        }
        	if($type&&$type=='team'){
        		$orderby ='ROUND(SUM(money)/10000,2) desc';
        		$group = 'report_team_list.team_code';
        	}elseif($type&&$type=='organ'){
        		$orderby ='ROUND(SUM(money)/10000,2) desc';
        		$group = 'report_organ_list.organ_code';
        	}else{
        		$orderby ='ROUND(SUM(money)/10000,2) desc';
        		$group = 'user_code';
        	}
        $clos= 'date,user_code,username,ROUND(SUM(new_money)/10000,2) new_money, SUM(new_oder) new_oder,ROUND(SUM(week_money)/10000,2) week_money,SUM(week_oder) week_oder,ROUND(SUM(month_money)/10000,2) month_money, SUM(month_oder) month_oder,ROUND(SUM(year_money)/10000,2) year_money, SUM(year_oder) year_oder,ROUND(SUM(senson_money)/10000,2) senson_money, SUM(senson_oder) senson_oder ,ROUND(SUM(money)/10000,2) money,sum(num) num,report_team_list.team_code,report_organ_list.organ_code';
        if(isset($datetype)&&$datetype){
        	if($datetype=='month'){
        		$listing = Report_Model_teammonth::instance()->fetchByteam($where, $orderby,$group, $perpage, $offset,$clos);
        		$data['count'] = count($listing);
        	}else if($datetype=='year'){
        		$listing = Report_Model_teamyear::instance()->fetchByteam($where, $orderby,$group, $perpage, $offset,$clos);
        		$data['count'] = count($listing);
        	}else{
        		$listing = Report_Model_team::instance()->fetchByteam($where, $orderby,$group, $perpage, $offset,$clos);
        		$data['count'] = count($listing);
        	}
        }else{
        	$listing = Report_Model_team::instance()->fetchByteam($where, $orderby,$group, $perpage, $offset,$clos);
        	$data['count'] = count($listing);
        }
        $data['page'] = $page;
        $data['perpage'] = $perpage;
        
        $cmoney = $cnum = $cname = array();
        if($listing){
        	if($type){
	        	foreach($listing as $k=>$v){
	        		if($type=='team'){
		        		$listing[$k]['user_code'] = $v['team_code'];
		        		$listing[$k]['username'] = $v['team_name'];
	        		}elseif($type=='organ'){
	        			$listing[$k]['user_code'] = $v['organ_code'];
	        			$listing[$k]['username'] = $v['organ_name'];
	        		}
	        	}
        	}
        	foreach($listing as $kk=>$kv){
        		if($kv['username']){
        			$cname[]=$kv['username'];
        		}else{
        			$cname[]=$kv['user_code'];
        		}
        		$cmoney[] = $kv['money'];
        		$cnum[] = $kv['num'];
        	}
        }
        $data['cname'] = $cname;
        $data['cmoney'] = $cmoney;
        $data['cnum'] = $cnum;
        $data['order_type'] = $order_type;
        $data['listing'] = $listing ? $listing : array();
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'team.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }
}