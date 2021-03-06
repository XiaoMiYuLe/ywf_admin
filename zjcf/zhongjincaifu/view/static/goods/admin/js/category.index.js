$(document).ready(function() {
	refreshListing();
	
    /**
     * 刷新搜索
     */
    $(".action-refresh,#action_search").on('click',function(){
    	$('#content_listing').datagrid('reload');
    	return false;
    });
    
    /**
	 * 关键字搜索 - 支持回车
	 */
	$('input[name=key]').on('keypress', function (event) {
	    if (event.which == '13') {
	        $('#content_listing').datagrid("reload");
	        return false;
	    }
	});
    
    /**
     * 单个删除分类
     */
    $('body').delegate('#content_listing .operate-delete', 'click', function(){
    	var tr=$(this).closest('tr'),cid = tr.find(".select-single").val();
    	if(!cid || !confirm('确定要删除此分类吗？')) return false;
    	$.ajax({
	    	type:'post',
	        url:'/goodsadmin/category/delete',
	        data:'category_id=' + cid,
	        dataType:'json',
	        timeout:10000,
	        beforeSend:function(){$(tr).addClass('remove');},
	        success:function(data){
	    		if (data.status == 0) {
	    			$(tr).fadeIn().remove();
	    		} else {
	    			alert(data.error);
	    		}
	    		return false;
	    	},complete:function(){$(tr).removeClass('remove');}
	    	
	    });
	});
    
    /**
     * 批量删除分类
     */
    $('body').delegate('#action_delete', 'click', function(){
		var id_arr = new Array();
		var i = 0;
		$('#content_listing').find('.select-single').each(function(){
			if ($(this).is(':checked')) {
				id_arr[i] = $(this).val();
				i++;
			}
		});
		var id = id_arr.join(',');
		
		if (! id) {
			return false;
		}
		
		var del = confirm('确定要删除所选分类吗？');
		if (! del) {return false;}
		
		/* 执行删除 */
		$.ajax({
	    	type:'post',
	        url:'/goodsadmin/category/delete',
	        data:'category_id=' + id,
	        dataType:'json',
	        timeout:10000,
	        success:function(data){
	    		if (data.status == 0) {
	    			$('#content_listing').find('.select-single:checked').parent().parent().remove();
	    		} else {
	    			alert(data.error);
	    		}
	    		return false;
	    	}
	    });
	 });
    
  //重新生成虚拟分类缓存
	$('.action-virtual').click(function(){
		$.ajax({
	    	type:'post',
	        url:'/goodsadmin/category/updateCacheVirtual',
	        data:'category_id=0',
	        dataType:'html',
	        timeout:60000,
	        success:function(status){
	        	if (status == 0) {
	        		alert('生成完毕');
	        	} else {
	        		alert('请稍候重试');
	        	}
	    		return false;
	    	}
	    });
	});
});

/**
 * datagrid 加载列表
 */
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
	        var url = '/goodsadmin/category';
	        var self = this;
	        
	        setTimeout(function () {
	
	            var data = $.extend(true, [], self._data);
	
	            $.ajax(url, {
	                data: {
	                	rstype:"json",
	                	pageIndex: options.pageIndex,
	                    pageSize: options.pageSize,
	                    key:$("input[name='key']").val()
	                },
	                dataType: 'json',
	                async: true,
	                type: 'GET'
	            }).done(function (response) {
	            	var data = response.data.category;
                    if (! data) {
                    	return false;
                    }

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
	
	$('#content_listing').datagrid({
	    dataSource: new DataGridDataSource({
	        // Column definitions for Datagrid
	        columns: [
	            {
	                property: 'checkbox',
	                label: ''
	            },
	            {
	                property: 'category_id',
	                label: 'ID',
	                sortable: true
	            },
	            {
	            	property: 'category_name',
	            	label: '分类名称',
	            	sortable: false
	            },
	            {
	            	property: 'parent_name',
	            	label: '父级分类名称',
	            	sortable: false
	            },
	            {
	            	property: 'sort_order',
	            	label: '序号',
	            	sortable: false
	            },
	            {
	            	property: 'operate',
	                label: '操作'
	            }
	        ],
	        formatter: function (items) {
	            $.each(items, function (index, item) {
	            	item.checkbox = '<input type="checkbox" name="post[]" class="select-single" value="' + item.category_id + '">';
	            	
	            	item.operate  = '<a href="/goodsadmin/category/bindBrand/?category_id=' + item.category_id + '" class="load-content" title="绑定品牌"><i class="fa fa-bitcoin"></i></a>&nbsp;&nbsp;';
	            	item.operate += '<a href="/goodsadmin/category/bindProperty/?category_id=' + item.category_id + '" class="load-content" title="绑定属性"><i class="fa fa-gear"></i></a>&nbsp;&nbsp;';
	            	item.operate += '<a href="/goodsadmin/category/edit/?category_id=' + item.category_id + '" data-toggle="ajaxModal" class="operate-edit" title="编辑"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
	            	item.operate += '<a href="javascript:;" class="operate-delete" title="删除"><i class="fa fa-times"></i></a>';
	            	
	            });
	        }
	    }),
	    loadingHTML: '<span><img src="/static/panel/img/loading.gif"><i class="fa fa-info-sign text-muted" "></i>正在加载……</span>',
	    itemsText: '项',
	    itemText: '项',
	    dataOptions: { pageIndex: 0, pageSize: 15 }	
	});
}