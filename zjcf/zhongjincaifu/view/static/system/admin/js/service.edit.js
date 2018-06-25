$(document).ready(function() {

	/**
	 * 提交按钮处理
	 */
	$(".input-submit").click(function(){
		var submit_type = $(this).attr("data_submit_type");
		switch (submit_type) {
			case 'submit_cancel' : form_cancel(); break;
			case 'submit_save_back' : back_listing = true; form_submit(); break;
			case 'submit_save_continue' : back_listing = false; form_submit(); break;
		}
	});
});

/**
 * 取消处理
 */
function form_cancel() {
	history.go(-1);
}

/**
 * 表单提交处理
 */
function form_submit() {
	notice('edit_notice', img_loading_small, false);
	
	if (! $("select[name=cat_id]").val()) {
		notice('edit_notice', img_delete + " 服务类型不能为空", true, 5000);
		return false;
	}
	
    var falg = 0;
    $("input[name='service_sale_type[]']:checkbox").each(function () {
        if ($(this).attr("checked")) {
            falg += 1;
        }
    })
    if(falg==0){
        alert("最少选择一个服务性质");
    }
    
	var ser_sale_id = $("input[name=ser_sale_id]").val();
	$('textarea[name="ser_sale_detail"]').val(ckeditor1.getData());
	$(".input-submit").attr('disabled', true);
	
	var saveCallBack;
	if (ser_sale_id == '' || ser_sale_id == 0) {
		saveCallBack = form_save_added;
	} else {
		$("#edit_form").attr("action", "/articleadmin/index/servicesaleedit");
		saveCallBack = form_save_edited;
	}
	
	var options = {
            dataType:'json',
            timeout:60000,
            success:saveCallBack
    };
    $("#edit_form").ajaxSubmit(options);
    return false;
}

/**
 * 添加成功，返回处理
 */
function form_save_added(data, textStatus) {
    if (data.status === 0) {
        notice('edit_notice', img_done + ' 添加成功!', true, 5000);
        
        // 判断是否返回列表管理
        if (back_listing == true) {
        	history.back(-1);
        }
    } else {
    	notice('edit_notice', img_delete + " " + data.error, true, 5000);
    }
    $(".input-submit").removeAttr('disabled');
}


/**
 * 编辑成功，返回处理
 */
function form_save_edited(data, textStatus) {
    if (data.status === 0) {
        notice('edit_notice', img_done + ' 编辑成功!', true, 5000);
        history.back(-1);
    } else {
    	notice('edit_notice', img_delete + " " + data.error, true, 5000);
    }
    $(".input-submit").removeAttr('disabled');
}