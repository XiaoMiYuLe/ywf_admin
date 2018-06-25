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
 *支付接口（绑卡）
 * @author Administrator
 *
 */
class Api_Pay_Pay
{
    /**
     * 默认返回数据
     */
	protected static $_res = array('status' => 0, 'msg' => '', 'data' => '');
	
    public static function run($params = null)
    {
        /* 验证是否有数据  */
		$res = self::validate($params);
		if ($res['status'] == 0) {
			try {
			    //支付类型：1：绑卡 2：订单支付 3：充值
                    
                    //绑卡的状态
                	if($res['data']['type']==1){
                		$bank_use = Cas_Model_Bank::instance()->fetchByWhere("userid='{$res['data']['member_id']}' and is_use=1 and is_del=0");
                		if($bank_use){
			              throw new Zeed_Exception('此用户已绑定银行卡，请勿在进行绑定');
			            } 
                	}
			        $data['userid'] = $res['data']['member_id'];
			        $data['order_no'] = $res['data']['order_no'];
			        $data['type'] = $res['data']['type'];
			        $data['ctime'] =  date(DATETIME_FORMAT);
			        $id = Cas_Model_Pay::instance()->addForEntity($data);

			        $set = Support_Reapal_pay_payResult::run($res['data']);			        
			        //银行如果是同步通知就好返回0000 异步通知返回3083
			        if ($set['status']!=0000 && $set['status']!=3083 ) {
			        	$mtime =  date('Y-m-d H:i:s');
			        	Cas_Model_Pay::instance()->update(array('code'=>$set['status'],'msg'=>$set['error'],'mtime'=>$mtime),"order_no = '{$data['order_no']}'");
			            throw new Zeed_Exception($set['error']);
			        }
                        
			       

			    $res['data'] = $set['data'];
			    $res['error'] = $set['error'];
			    
            /* 返回错误信息  */
			} catch(Zeed_Exception $e) {
				$res['status'] = 1;
				if ($set['status']==3081) {
			        $res['error'] = '银行处理出现延迟，为避免重复扣款，请稍后查询。';
			    }else{
                    $res['error'] = '支付失败。错误信息：' . $e->getMessage();
			    }
				return $res;
			}catch(Exception $e) {
				$res['status'] = 1;
				$res['error'] = '错误信息：网络请求异常，请稍后再试';
				return $res;
			}
		}
		/* 返回数据 */
		return $res;
	}
	
	/**
	 * 验证参数
	 */
	public static function validate($params)
	{
	    /* 验证是否有数据  */
		if (!isset($params['merchant_id']) || ! $params['merchant_id']) {
			self::$_res['status'] = 1;
			self::$_res['error'] = '商户号未提供';
			return self::$_res;
		}
		if (!isset($params['order_no']) || ! $params['order_no']) {
			self::$_res['status'] = 1;
			self::$_res['error'] = '商户订单号未提供';
			return self::$_res;
		}
		if (!isset($params['check_code']) || ! $params['check_code']) {
			self::$_res['status'] = 1;
			self::$_res['error'] = '短信验证码未提供';
			return self::$_res;
		}
		if (!isset($params['type']) || ! $params['type']) {
		    self::$_res['status'] = 1;
		    self::$_res['error'] = '支付类型未提供';
		    return self::$_res;
		}
		if (!isset($params['member_id']) || ! $params['member_id']) {
		    self::$_res['status'] = 1;
		    self::$_res['error'] = '用户id未提供';
		    return self::$_res;
		}
		
		self::$_res['data'] = $params;
		return self::$_res;
	}
}