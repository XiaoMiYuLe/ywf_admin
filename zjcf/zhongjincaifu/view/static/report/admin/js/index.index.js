$(document).ready(function() {
	refreshListing();
    
	/**
	 * 刷新或搜索
	 */
	$('body').delegate('.action-refresh,#action_search', 'click', function(){
		var url = '/reportadmin/?update=1';
		 if($('input[name=type]').val()){
			 url += '&type='+$('input[name=type]').val();
		 }
		 if($('input[name=goods_name]').val()){
			 url += '$goods_name='+$('input[name=goods_name]').val();
		 }
		 if($('input[name=ctime]').val()){
			 url +=  '&ctime='+$('input[name=ctime]').val();
		 }
		location.replace(url);
		$('#content_listing').datagrid('reload');
		refreshListing();
	});
	
	/**
	 * 扔进回收站 - 单条
	 */
	$("#content_listing").delegate('.operate-trash', 'click', function(){
		var content_id = $(this).attr("content_id");
		doTrashContent(content_id);
	});
	
	/**
	 * 预处理日期选择控件
	 */
	/*$('.datepicker-input').datepicker();*/
	/**
	 *  日期控件格式化
	 */
	$(".day").focus(function(){
		      WdatePicker({dateFmt:'yyyy-MM-dd'});
	});
	$(".year").focus(function(){
	      WdatePicker({dateFmt:'yyyy'});
	});
	$(".month").focus(function(){
	      WdatePicker({dateFmt:'yyyy-MM'});
	});
	
});



function refreshListing() {

	/* fuelux datagrid */
	var DataGridDataSource = function (options) {
	    this._formatter = options.formatter;
	    this._columns = options.columns;
	    this._delay = options.delay;
	};
	
	DataGridDataSource.prototype = {
	    columns: function () {
	        return this._columns;
	    },
	    data: function (options, callback) {
	        var url = '/reportadmin/';
	        var self = this;
	        setTimeout(function () {
	            var data = $.extend(true, [], self._data);
	            $.ajax(url, {
	                data: {
	                	rstype:"json",
	                	pageIndex: options.pageIndex,
	                    pageSize: options.pageSize,
	                    goods_name: $('input[name=goods_name]').val(),
	                    type: $('input[name=type]').val(),
	                    ctime: $('input[name=ctime]').val()
	                },
	                dataType: 'json',
	                async: true,
	                type: 'GET'
	            }).done(function (response) {
	            	var data = response.data.listing;
                    if (! data) {
                    	return false;
                    }
                    var stop = location.href.indexOf('?');
                    var start = location.href.indexOf('/reportadmin');
                    var href = location.href.substring(location.href.indexOf('/reportadmin'));
                    if(stop>start){
                    	href = location.href.substring(start,stop);
                    }
                    $(".load-content").each(function(){
                        if($(this).attr("href")==href){
                           $(this).parent().addClass('active');
                           $(this).parent().parent().parent().addClass('active').siblings().children("ul").hide();
                           $(this).parent().parent().parent().addClass('active').siblings().removeClass('active');
                        }else{
                        	 $(this).parent().removeClass('active');
                        }
                    });
                    var count=response.data.count;//设置data.total
                    // PAGING
                    var startIndex = options.pageIndex * options.pageSize;
                    var endIndex = startIndex + options.pageSize;
                    var end = (endIndex > count) ? count : endIndex;
                    var pages = Math.ceil(count / options.pageSize);
                    var page = options.pageIndex + 1;
                    var start = startIndex + 1;

                    if (self._formatter) self._formatter(data);

                    callback({ data: data, start: start, end: end, count: count, pages: pages, page: page });
                }).fail(function (e) {

                });
	        }, self._delay);
	    }
	};
	try{
	$('#content_listing').datagrid({
	    dataSource: new DataGridDataSource({
	        // Column definitions for Datagrid
	        columns: [
	            {
	                property: 'id',
	                label: '序列号',
	                sortable: false
	            },
	            {
	                property: 'date',
	                label: '日期',
	                sortable: false
	            },
	            {
	                property: 'goods_name',
	                label: '产品名称',
	                sortable: false
	            },
	            {
	                property: 'money',
	                label: '新增金额(万元)',
	                sortable: false
	            },
	            {
	                property: 'allmoney',
	                label: '累计总额(万元)',
	                sortable: false
	            },
	            {
	                property: 'num',
	                label: '新增交易笔数',
	                sortable: false
	            },	            
	            {
	            	property: 'allnum',
	            	label: '交易总笔数',
	            	sortable: false
	            }
	        ]
	    }),
	    loadingHTML: '<span><img src="/static/panel/img/loading.gif"><i class="fa fa-info-sign text-muted" "></i>正在加载……</span>',
	    itemsText: '项',
	    itemText: '项',
	    dataOptions: { pageIndex: 0, pageSize: 15 }	
	});
	}catch(e){
		if(e instanceof TypeError)
			location.reload();
		}
}