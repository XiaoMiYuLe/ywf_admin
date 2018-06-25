<?php

class UserController extends ReportAdminAbstract
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
//         	$day = $now;
//         	$where .= " and date = "."'{$day}'";
        }
        $orderby = 'date DESC';
        $listing = Report_Model_user::instance()->fetchByWhere($where, $orderby, $perpage, $offset);
//     $count = Report_Model_user::instance()->getcolletday($day,$type);
		$user = $ecoman = $card = $cdate = $day_online = array();
		if($listing){
			foreach ($listing as $lk => $lv){
				$listing[$lk]['day_outline']  = $lv['day_user']-$lv['day_online'];
				if(isset($type)&&$type){
					$listing[$lk]['day_user']= $lv[$type.'_user'];
					$listing[$lk]['day_ecoman']= $lv[$type.'_ecoman'];
					$listing[$lk]['day_card']= $lv[$type.'_card'];
					$listing[$lk]['day_outline'] = $lv[$type.'_user']-$lv[$type.'_online'];
					$listing[$lk]['day_online'] = $lv[$type.'_online'];
				}
					$user[]= $listing[$lk]['day_user'];
					$ecoman[]= $listing[$lk]['day_ecoman'];
					$card[]= $listing[$lk]['day_card'];
					$cdate[]=$listing[$lk]['date'];
					$day_online[]=$listing[$lk]['day_online'];
			}
		}
		$data['user'] = $user;
		$data['ecoman'] = $ecoman;
		$data['card'] = $card;
		$data['cdate'] = $cdate;
		$data['day_online'] = $day_online;
        $data['listing'] = $listing ? $listing : array();
        
        $data['count'] = Report_Model_user::instance()->getCount($where);
        $data['page'] = $page;
        $data['perpage'] = $perpage;
        $data['order_type'] = $order_type;
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'user.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }
}