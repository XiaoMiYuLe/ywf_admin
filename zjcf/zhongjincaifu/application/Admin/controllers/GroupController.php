<?php
/**
 * Zeed Platform Project
 * Based on Zeed Framework & Zend Framework.
 * 
 * LICENSE
 * http://www.zeed.com.cn/license/
 * 
 * @category   Zeed
 * @package    Zeed_ChangeMe
 * @subpackage ChangeMe
 * @copyright  Copyright (c) 2010 Zeed Technologies PRC Inc. (http://www.zeed.com.cn)
 * @author     Zeed Team (http://blog.zeed.com.cn)
 * @since      2011-3-21
 * @version    SVN: $Id$
 */

class GroupController extends AdminAbstract
{

    public $perpage = 20;

    public $count;

    public $page;

    /**
     * 用户组列表管理
     */
    public function index ()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        
        $parentid = $this->input->get('parentid', 0);
        
        $data['parentid'] = $parentid;
        $data['groups'] = GroupModel::instance()->getGroups();
        
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'group.index');
        return parent::multipleResult(self::RS_SUCCESS);
    }

    /**
     * 添加用户组
     */
    public function add ()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        
        if ($this->input->isPOST()) {
            $this->addSave();
            return self::RS_SUCCESS;
        }
        
        $data['parent_groups'] = GroupModel::instance()->getGroups(0);
        
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'group.edit');
        return parent::multipleResult(self::RS_SUCCESS);
    }
    


    /**
     * 添加用户组 - 保存
     */
    public function addSave ()
    {
        $set = $this->_validateGroup();
        if ($set['status'] == 0) {
            try {
                GroupModel::instance()->addGroup($set['data']);
            } catch (Zeed_Exception $e) {
                $this->setStatus(1);
                $this->setError('Add group failed : ' . $e->getMessage());
                return false;
            }
            return true;
        }
        
        $this->setStatus($set['status']);
        $this->setError($set['error']);
        return false;
    }

    public  function edit2()
    {   
        $order_no = (int) $this->input->query('groupid');
        $order = Cas_Model_Pay::instance()->fetchByWhere("order_no = '{$order_no}' and code ='0000'");
        if(!empty($order)){
            $order = $order[0];
             
            //支付类型：1：绑卡支付 2：订单支付 3：充值支付
            if($order['type']==1){
                //用户是否存在
                $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$order['userid']}' and status=0");
                if(empty($ExistUser)){
                    throw new Zeed_Exception('用户不存在或被冻结');
                }
                //cas_bank表 is_use=1
                $bank = Cas_Model_Bank::instance()->update(array('is_use'=>1),"order_no = '{$data['order_no']}' and userid='{$order['userid']}' and is_del=0");
                
                $cas_bank = Cas_Model_Bank::instance()->fetchByWhere("order_no = '{$data['order_no']}' and userid='{$order['userid']}'");
                //小额扣款至您的平台账户余额
                $asset = $ExistUser[0]['asset'] +2.18;
                $user = Cas_Model_User::instance()->update(array('username'=>$cas_bank[0]['cardholder'],'idcard'=>$cas_bank[0]['cert_no'],'asset'=>$asset),"userid='{$order['userid']}' and status=0");
                //资金明细表
                $data['flow_asset'] = $ExistUser[0]['asset']+2.18;
                $data['userid'] = $order['userid'];
                $data['order_no'] = $order['order_no'];
                $data['money'] = $data['total_fee'];
                $data['status'] = '+';
                $data['ctime'] = date('Y-m-d H:i:s');
                $data['mtime'] = date('Y-m-d H:i:s');
                $data['type'] = 1;
                $data['pay_type'] = 2;
                $log = Cas_Model_Record_Log::instance()->addForEntity($data);
            }elseif($order['type']==2){
                $goods_order = Bts_Model_Order::instance()->fetchByWhere("order_no = '{$order['order_no']}'");
                $goods_order = $goods_order[0];
                //不同的产品模式 bts_order表 is_pay=1  新手和直购 ：order_status=2 ，预约order_status=1
                if(!empty($goods_order)){
                    if($goods_order['goods_pattern']==1 || $goods_order['goods_pattern']==2){
                        $paytime = date('Y-m-d H:i:s');
                        $update_order = Bts_Model_Order::instance()->update(array('is_pay'=>1,'pay_time'=>$paytime,'pay_type'=>2,'is_del'=>0,'order_status'=>2),"order_no = '{$order['order_no']}' and is_del=1 and is_pay=0");
                    }
                    //产品剩余额度
                    $buy_money = $goods_order['buy_money'];
                    $goods = Goods_Model_List::instance()->fetchByWhere("goods_id = '{$goods_order['goods_id']}' and is_del=0");


                    //代金券状态处理 已使用:voucher_status=2
                    if($goods_order['is_voucher']==1){
                        $use_time = date(DATETIME_FORMAT);
                        $voucher_status = Cas_Model_User_Voucher::instance()->update(array('voucher_status'=>2,'order_id'=>"{$goods_order['order_id']}",'use_time'=>"{$use_time}"),"id={$goods_order['voucher']}");
                    }
                    //佣金处理,新手类产品不存在佣金
                    if($goods_order['goods_pattern']==2){
                        self::brokerage($order['order_no']);
                    }
                    //录入资金明细表,先充值
                    //计算余额,充值
                    $calculate_asset_recharge = self::calculate_asset_plus($order['userid'],$goods_order['real_money']);
                    if(($log_recharge = self::record_log($calculate_asset_recharge, $order['userid'], $order['order_no'], $goods_order['real_money'], '+', 1,2,$goods_order['order_id']))==false){
                        throw new Zeed_Exception('支付失败');
                    }
                    //录入资金明细表,不变
                    //计算余额,不变
                    $calculate_asset = self::calculate_asset($order['userid'],0);
                    if(($log = self::record_log($calculate_asset, $order['userid'], $order['order_no'], $goods_order['real_money'], '-', 3,2,$goods_order['order_id']))==false){
                        throw new Zeed_Exception('支付失败');
                    }
                    //将用户置为非新手
                    Cas_Model_User::instance()->update(array('is_buy'=>1),"userid = {$order['userid']}");
                    
                }else{
                    throw new Zeed_Exception('订单不存在或已支付');
                }
            
               
            }elseif($order['type']==3){
                //获取用户信息
                $user_info = self::getUser($order['userid']);
                //计算余额
                $calculate_asset = self::calculate_asset_plus($order['userid'],  $order['real_money']);
                //充值记录表
                $recharge_log = self::recharge_log($calculate_asset, $order['userid'],$user_info[1][0]['phonebankcard'],$user_info[1][0]['bank_name'],$user_info[1][0]['bank_no'], $user_info[1][0]['subbank_name'],(int)$order['real_money'],$order['order_no']);
                //用户余额
               if(($asset = self::asset($order['userid'], $order['real_money']))==false){
                   $recharge_status = Recharge_Model_List::instance()->update(array('recharge_status'=>2),"recharge_id={$recharge_log}");
                    throw new Zeed_Exception('充值失败');
               }
               //资金流水表
               if(($log = self::record_log($calculate_asset, $order['userid'], $order['order_no'],$order['real_money'], '+', 1,2,null))==false){
                   throw new Zeed_Exception('充值失败');
               }
               
            }
        }else{
            throw new Zeed_Exception("支付失败");
        }
        return self::$_res;
    }
    
    //获取用户信息
    public static function getUser($user_id){
        if(!$user_id){
            throw new Zeed_Exception('用户未登录');
        }
        //用户基本信息
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("status=0 and userid={$user_id}");
        if(empty($ExistUser)){
            throw new Zeed_Exception('用户不存在或被冻结');
        }
        //用户绑卡信息
        $user_bank = Cas_Model_Bank::instance()->fetchByWhere("bank_id = '{$ExistUser[0]['bank_id']}' and is_use=1 and is_del=0");
        $user_info = array_push($ExistUser, $user_bank);
        if(!$user_info){
            throw new Zeed_Exception('用户无效，请重新登录！');
        }
        return $ExistUser;
    }
    //购买人数加1
    public static function addOne($goods_id){
         $good = Goods_Model_List::instance()->fetchByWhere("goods_id='{$goods_id}'");
         $num = $good[0]['buy_num']+1;
         Goods_Model_List::instance()->update(array('buy_num'=>$num),"goods_id = '{$goods_id}'");
    }
    
    //加用户余额
    public static function asset($userid,$total_fee){
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$userid}' and status=0");
        $asset = $ExistUser[0]['asset'] +$total_fee;
        $user = Cas_Model_User::instance()->update(array('asset'=>$asset),"userid='{$userid}' and status=0");
        if(!$user){
           return false;
        }
        return true;
    }
    
    //减用户余额
    public  function asset_reduce($userid,$total_fee){
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$userid}' and status=0");
        $asset = $ExistUser[0]['asset'] -$total_fee;
        $user = Cas_Model_User::instance()->update(array('asset'=>$asset),"userid='{$userid}' and status=0");
        if(!$user){
            return false;
        }
        return true;
    }
    
    //计算用户余额 +
    public static function calculate_asset_plus($userid,$total_fee){
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$userid}' and status=0");
        $asset = $ExistUser[0]['asset'] +$total_fee;
        return $asset;
    }
    
    //计算用户余额  不变
    public static function calculate_asset($userid,$total_fee){
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$userid}' and status=0");
        $asset = $ExistUser[0]['asset'];
        return $asset;
    }
    
    //计算用户余额-
    public static function calculate_asset_reduce($userid,$total_fee){
        $ExistUser = Cas_Model_User::instance()->fetchByWhere("userid='{$userid}' and status=0");
        $asset = $ExistUser[0]['asset'] -$total_fee;
        return $asset;
    }
    
    //充值记录表
    public static function recharge_log($asset,$userid,$phone,$bank_name,$bank_no,$opening_bank,$recharge_money,$platform_serial_number){
        $data['userid'] = $userid;
        $data['phone'] = $phone;
        $data['bank_name'] = $bank_name;
        $data['bank_no'] = $bank_no;
        $data['opening_bank'] = $opening_bank;
        $data['recharge_money'] = $recharge_money;
        $data['asset'] = $asset;
        $data['platform_serial_number'] = $platform_serial_number;
        $data['recharge_status'] = 1;//默认充值成功
        $data['ctime'] = date('Y-m-d H:i:s');
        $recharge_log = Recharge_Model_List::instance()->addForEntity($data);
        if(!$recharge_log){ 
            return false;
        }
        return $recharge_log;
    }
    
    
    //资金流水表
    public static function record_log($asset,$userid,$order_no,$total_fee,$status,$type,$pay_type,$order_id){
         $data['flow_asset'] = $asset;
         $data['userid'] = $userid;
         $data['order_no'] = $order_no;
         $data['money'] = $total_fee;
         $data['status'] = $status;
         $data['ctime'] = date('Y-m-d H:i:s');
         $data['mtime'] = date('Y-m-d H:i:s');
         $data['type'] = $type;
         $data['pay_type'] = $pay_type;
         $data['order_id'] = $order_id;
         $log = Cas_Model_Record_Log::instance()->addForEntity($data);
         if(!$log){
             return false;
         }
         return $log;
    }
    //佣金处理
    public static function brokerage($order_no)
    {
     $order = Cas_Model_Pay::instance()->fetchByWhere("order_no = '{$order_no}'");
         $order = $order[0];
         //查订单
         $goods_order = Bts_Model_Order::instance()->fetchByWhere("order_no = '{$order['order_no']}'");
         //查用户
         $user_order =Cas_Model_User::instance()->fetchByWhere("userid = '{$goods_order[0]['userid']}'");
         //查产品
         $good_pay = Goods_Model_List::instance()->fetchByWhere("goods_id='{$goods_order[0]['goods_id']}'");
         if($good_pay['is_new']!=1){
             //佣金
             $parents =  Cas_Model_User::instance()->fetchParents($order['userid']);
             if(!empty($parents)){
                 //一级客户
                 if($parents[0]['one'] && ($parents[0]['one']!=2446)){
                     $data['userid'] = $parents[0]['one'];//用户id
                     $data['user_grade'] = 1;//客户等级
                     //查一级客户
                     $user_one = Cas_Model_User::instance()->fetchByWhere("userid ='{$parents[0]['one']}' and status=0");
                     $data['username'] = $user_order[0]['username'];//客户名称
                     //var_dump( $data['username']);die;
                     //查订单
                     $order_pay = Bts_Model_Order::instance()->fetchByWhere("order_no='{$order_no}'");
                     $data['order_id'] = $order_pay[0]['order_id'];//订单id
                     $data['investment_amount'] = $order_pay[0]['buy_money'];//投资金额
                     $data['order_time'] = $order_pay[0]['ctime'];//下单时间
                     //查佣金设定表
                     $brokerage_setting = Brokerage_Model_Setting::instance()->fetchByWhere("1=1");
                     //产品佣金比例*佣金设定比例
                     $data['brokerage_ratio'] = $good_pay[0]['goods_broratio']*0.01*$brokerage_setting[0]['first_brokerage'];
                     //提成计算
                     $data['expected_money'] = round($brokerage_setting[0]['first_brokerage']*0.01*$order_pay[0]['buy_money']*$good_pay[0]['goods_broratio']*0.01,2);//预计提成
                     $data['brokerage_status'] = 1;//佣金状态
                     Cas_Model_User_Brokerage::instance()->addForEntity($data);
                 }
                 //二级客户
                 if($parents[0]['two'] && ($parents[0]['two']!=2446)){
                     $data['userid'] = $parents[0]['two'];//用户id
                     $data['user_grade'] = 2;//客户等级
                     //查一级客户
                     $user_one = Cas_Model_User::instance()->fetchByWhere("userid ='{$parents[0]['two']}' and status=0");
                     $data['username'] = $user_order[0]['username'];//客户名称
                     //var_dump( $data['username']);die;
                     //查订单
                     $order_pay = Bts_Model_Order::instance()->fetchByWhere("order_no='{$order_no}'");
                     $data['order_id'] = $order_pay[0]['order_id'];//订单id
                     $data['investment_amount'] = $order_pay[0]['buy_money'];//投资金额
                     $data['order_time'] = $order_pay[0]['ctime'];//下单时间
                     //产品佣金比例*佣金设定比例
                     $data['brokerage_ratio'] = $good_pay[0]['goods_broratio']*0.01*$brokerage_setting[0]['second_brokerage'];
                     //查佣金设定表
                     $brokerage_setting = Brokerage_Model_Setting::instance()->fetchByWhere("1=1");
                     //提成计算
                     $data['expected_money'] = round($brokerage_setting[0]['second_brokerage']*0.01*$order_pay[0]['buy_money']*$good_pay[0]['goods_broratio']*0.01,2);//预计提成
                     $data['brokerage_status'] = 1;//佣金状态
                     Cas_Model_User_Brokerage::instance()->addForEntity($data);
                 }
                 //三级客户
                 if($parents[0]['three'] && ($parents[0]['three']!=2446)){
                     $data['userid'] = $parents[0]['three'];//用户id
                     $data['user_grade'] = 3;//客户等级
                     //查一级客户
                     $user_one = Cas_Model_User::instance()->fetchByWhere("userid ='{$parents[0]['three']}' and status=0");
                     $data['username'] = $user_order[0]['username'];//客户名称
                     //var_dump( $data['username']);die;
                     //查订单
                     $order_pay = Bts_Model_Order::instance()->fetchByWhere("order_no='{$order_no}'");
                     $data['order_id'] = $order_pay[0]['order_id'];//订单id
                     $data['investment_amount'] = $order_pay[0]['buy_money'];//投资金额
                     $data['order_time'] = $order_pay[0]['ctime'];//下单时间
                     //查佣金设定表
                     $brokerage_setting = Brokerage_Model_Setting::instance()->fetchByWhere("1=1");
                     //产品佣金比例*佣金设定比例
                     $data['brokerage_ratio'] = $good_pay[0]['goods_broratio']*0.01*$brokerage_setting[0]['third_brokerage'];//产品佣金比例
                     //提成计算
                     $data['expected_money'] = round($brokerage_setting[0]['third_brokerage']*0.01*$order_pay[0]['buy_money']*$good_pay[0]['goods_broratio']*0.01,2);//预计提成
                     $data['brokerage_status'] = 1;//佣金状态
                     Cas_Model_User_Brokerage::instance()->addForEntity($data);
                 }
             }
         }
    }

    /**
     * 修改用户组
     */
    public function edit1 ()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        $this->addResult(self::RS_INPUT, 'json');
        
        if ($this->input->isPOST()) {
            $this->editSave();
            return self::RS_SUCCESS;
        }
        
        $groupid = (int) $this->input->query('groupid');
        $group = GroupModel::instance()->fetchByPK($groupid);
        if (null === $group || ! is_array($group)) {
            $this->setStatus(1);
            $this->setError('The group is not exist.');
            return self::RS_SUCCESS;
        }
        
        $data['group'] = $group[0];
        $data['parent_groups'] = GroupModel::instance()->getGroups(0);
        
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'group.edit');
        return parent::multipleResult(self::RS_SUCCESS);
    }

    /**
     * 修改用户组 - 保存
     */
    public function editSave ()
    {
        $set = $this->_validateGroup();
        if ($set['status'] == 0) {
            try {
                GroupModel::instance()->updateGroup($set['data'], $set['data']['groupid']);
            } catch (Zeed_Exception $e) {
                $this->setStatus(1);
                $this->setError('Edit group failed : ' . $e->getMessage());
                return false;
            }
            return true;
        }
        
        $this->setStatus($set['status']);
        $this->setError($set['error']);
        return false;
    }

    /**
     * 设置用户组权限 -- 该功能没有完成
     */
    public function editPermission ()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        
        if ($this->input->isPOST()) {
            $this->editSavePermission();
            return self::RS_SUCCESS;
        }
        
        $groupid = (int) $this->input->query('groupid');
        $group = GroupModel::instance()->fetchByPK($groupid);
        if (null === $group || ! is_array($group)) {
            $this->setStatus(1);
            $this->setError('The group is not exist.');
            return self::RS_SUCCESS;
        }
        
        $data['group'] = $group[0];
        $data['permission'] = PermissionModel::instance()->getAllPermissions();
        
        $this->setData('data', $data);
        $this->addResult(self::RS_SUCCESS, 'php', 'group.edit.permission');
        return parent::multipleResult(self::RS_SUCCESS);
    }

    /**
     * 保存用户组－校验
     */
    private function _validateGroup ()
    {
        $res = array(
                'status' => 0,
                'error' => null,
                'data' => null
        );
        
        $res['data'] = array(
                'groupid' => $this->input->post('groupid'),
                'parentid' => $this->input->post('parentid'),
                'groupname' => $this->input->post('groupname'),
                'description' => $this->input->post('description')
        );
        
        // 数据验证
        if (empty($res['data']['groupname'])) {
            $res['status'] = 1;
            $res['error'] = '请填写完所有带红色星号的内容';
            return $res;
        }
        
        return $res;
    }
    
    /**
     * ajax - 删除用户组
     */
    public function delete()
    {
        $this->addResult(self::RS_SUCCESS, 'json');
        
        if (! $this->input->isAJAX()) {
            $this->setStatus(1);
            $this->setError('请求方式错误');
            return self::RS_SUCCESS;
        }
        
        $groupid = (int) $this->input->query('groupid');
        $del_user = (int) $this->input->query('del_user', 0);
        
        if ($groupid < 1) {
            $this->setStatus(1);
            $this->setError('用户组ID错误');
            return self::RS_SUCCESS;
        }
        
        /* 获取用户组信息 */
        $group = current(GroupModel::instance()->fetchByPK($groupid));
        
        /* 删除用户组下的所有用户 */
        if ($del_user == 1) {
            $users = UserGroupModel::instance()->fetchByGroupid($groupid);
            if (is_array($users) && count($users) > 0) {
                foreach ($users as $v) {
                    // 删除用户
                    UserModel::instance()->removeUser($v['userid']);
                    // 删除用户对应的权限关系
                    UserPermissionModel::instance()->delete("ptype = 'user' AND parameter='{$v['username']}' AND note = '{$v['username']}'");
                }
            }
        }
        
        /* 删除用户组与用户对应关系 */
        UserGroupModel::instance()->removeUserGroup($groupid);
        
        /* 删除用户组对应的权限关系 */
        UserPermissionModel::instance()->delete("ptype = 'group' AND parameter='{$groupid}' AND note = '{$group['groupname']}'");
        
        /* 删除用户组 */
        GroupModel::instance()->removeGroup($groupid);
        
        return self::RS_SUCCESS;
    }
}

// End ^ Native EOL ^ UTF-8