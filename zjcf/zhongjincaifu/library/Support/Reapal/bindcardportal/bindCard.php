<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>绑卡签约接口</title>
  </head>
  
  <body>
    <div align="center">
    <form action="bindCardResult.php" method="post">
    <table align="center">
    	<tr><td colspan="2" align="center"><h2>绑卡签约接口</h2></td></tr>
    	<tr><td>商户号：</td><td><input type="text" name="merchant_id" value=""/></td></tr>
		<tr><td>商户会员号：</td><td><input type="text" name="member_id" value=""/></td></tr>
		<tr><td>绑卡号：</td><td><input type="text" name="bind_id" value=""/></td></tr>
		<tr><td>交易金额：</td><td><input type="text" name="total_fee" value=""/>单位：元</td></tr>
    	<tr><td></td><td><input type="submit" value="提交"/></td></tr>		
    </table>
    </form>
    </div>
  </body>
</html>