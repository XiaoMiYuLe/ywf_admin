$(document).ready(function() {
    /**
	 * 返回
	 */
    $("#button_cancel").click(function(){
    	history.go(-1);
	});
    
    $(".button_bank").click(function(){
        $bank_id = $(this).val();
   	  ajax_post();
    });

    	function ajax_post(){
    	  $.post("/casadmin/index/delete",{bank_id:$bank_id},
    	  function(data){
    		 if(data.status==0){
    			alert('成功')
    			location.reload();
    		 }else{
    			 alert(data.error)
    			 }
    	  },
    	  "json");
    	}
});