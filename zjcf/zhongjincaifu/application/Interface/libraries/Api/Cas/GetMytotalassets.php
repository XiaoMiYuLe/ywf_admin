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

/**
 * 获取经纪人信息
 */
class Api_Cas_GetMytotalassets
{
    protected static $_res = array('status' => 0, 'error' => '', 'data' => '');
    protected static $_allowFields = array('userid');
    public static function run($params = null)
    {
        $res = self::validate($params);
        if ($res['status'] == 0) {
        	try {
        		/* 检查用户是否存在 */
                $userExists = Cas_Model_User::instance()->fetchByWhere( "userid= '{$res['data']['userid']}'");
    	        if (!$userExists) {
    	            throw new Zeed_Exception('该用户不存在，请重新输入');
    	        }
    	        
    	        /* 检查用户状态 */
    	        if($userExists[0]['status'] == 1 ){
    	            throw new Zeed_Exception('该账号已禁用，请重新输入');
    	        }
    	        
    	        $userExists = $userExists[0];

    	        //账户余额
    	        $asset = number_format($userExists['asset'],2);
    	        $asset = $asset?$asset :'0.00';
    	        
    	        //待结佣金
    	       /*  $brokerage_statusOne = Cas_Model_User_Brokerage::instance()->fetchByWhere("userid='{$res['data']['userid']}' and brokerage_status=1");
    	        
    	        if(!empty($brokerage_statusOne)){
    	            foreach ($brokerage_statusOne as $k=>&$v){
    	                $brokerage_one += (int)$v['expected_money'];
    	            }
    	        } */
    	        
    	        //冻结资金
    	        $brokerage_statusOne = Withdraw_Model_List::instance()->fetchByWhere("userid='{$res['data']['userid']}' and withdraw_status=1");
    	        if(!empty($brokerage_statusOne)){
    	            foreach ($brokerage_statusOne as $k=>&$v){
    	                $brokerage += $v['withdraw_money'];
    	            }
    	        } 
    	        
    	        $brokerage_one = number_format($brokerage,2);
    	        $brokerage_one = $brokerage_one?$brokerage_one :'0.00';
    	        
    	        //待收本金
    	        $principal = Bts_Model_Order::instance()->fetchByWhere("userid='{$res['data']['userid']}' and order_status<>4 and is_del=0 and is_pay=1 and goods_pattern<>4");

    	        if(!empty($principal)){
    	            foreach ($principal as $k=>&$v){
    	                $principal_all += $v['buy_money'];
    	            }
    	        }
    	        $principalall = number_format($principal_all,2);
    	        
    	        $principalall = $principalall?$principalall :'0.00';
    	        
    	        //总资产
    	        $all_asset =number_format($principal_all +$userExists['asset']+$brokerage,2);

                $data = array(
                    'userid'=>$res['data']['userid'],
                    'brokerage_one' =>$brokerage_one,
                    'principal_all' =>$principalall,
                    'all_asset' =>$all_asset,
                    'asset'=>$asset,
                );
                
		        $res['data'] = $data;
        	} catch (Zeed_Exception $e) {
        		$res['status'] = 1;
        		$res['error']  = '获取用户信息失败。错误信息：' . $e->getMessage();
        		return $res;
        	}
        }
        return $res;
    }
    
    /**
     * 验证参数
     */
    public static function validate($params)
    {
        
        /* 校验必填项 */
        if (! isset($params['userid']) || ! $params['userid']) {
            self::$_res['status'] = 1;
            self::$_res['error']  = '用户id未提供';
            return self::$_res;
        }
       
        /* 组织数据 */
        $set = array();
        foreach (self::$_allowFields as $f) {
            $set[$f] = isset($params[$f]) ? $params[$f] : null;
        }
        self::$_res['data'] = $set;
        
        return self::$_res;
    }
}

// End ^ Native EOL ^ encoding
