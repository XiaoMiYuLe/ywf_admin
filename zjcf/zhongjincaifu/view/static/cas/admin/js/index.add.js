$(document).ready(function() {
	/**
	 * 提交按钮处理
	 */
	$(".input-submit").click(function(){
		var submit_id = $(this).attr("id");
		switch (submit_id) {
			case 'submit_cancel' : group_cancel(); break;
			case 'submit_save_back' : back_listing = true; group_submit(); break;
			case 'submit_save_continue' : back_listing = false; group_submit(); break;
		}
	});
});


/**
 * 取消处理
 */
function group_cancel() {
	history.go(-1);
}

/**
 * 角色表单提交处理
 */
function group_submit() {
	notice('edit_notice', img_loading_small, false);
	
	if (! $("input[name=phone]").val()||! $("input[name=password]").val()||! $("input[name=repassword]").val()||! $("input[name=tcode]").val()||! $("input[name=name]").val()) {
		notice('edit_notice', img_delete + ' 请填写完所带红色星号的内容', true, 5000);
		return false;
	}
	
	if ($("input[name=password]").val() !=  $("input[name=repassword]").val()) {
		notice('edit_notice', img_delete + '输入密码不一致', true, 5000);
		return false;
	}
	
	var saveCallBack;
	
	$("#edit_form").attr("action", "/casadmin/index/add");
	saveCallBack = group_save_added;
	
	var options = {
            dataType:'json',
            timeout:60000,
            success:saveCallBack,
            error:ajaxError
    };
    $("#edit_form").ajaxSubmit(options);
    return false;
}

/**
 * 添加成功，返回处理
 */
function group_save_added(data, textStatus) {
    if(data.status === 0) {
        notice('edit_notice', img_done + ' 添加机构人员成功!', true, 5000);
        
        // 判断是否返回用户组列表管理
        if (back_listing == true) {
//        	history.back(-1);
        }else{
//        	history.back(0);
        }
    } else {
    	notice('edit_notice', img_delete + " " + data.error, true, 5000);
    }
    $(".input-submit").removeAttr('disabled');
}

