$(document).ready(function() {
	refreshListing();
    
	/**
	 * 刷新或搜索
	 */
	$('body').delegate('.action-refresh,#action_search', 'click', function(){
		$('#content_listing').datagrid('reload');
		return false;
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
	        var url = '/reportadmin/team';
	        var self = this;
	        setTimeout(function () {
	
	            var data = $.extend(true, [], self._data);
	            $.ajax(url, {
	                data: {
	                	rstype:"json",
	                	pageIndex: options.pageIndex,
	                    pageSize: options.pageSize,
	                    name: $('input[name=cname]').val(),
	                    type: $('input[name=type]').val(),
	                    datetype: $('input[name=datetype]').val(),
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
                    
	                if(response.data.cnum.length>0){
	                	var cnum = response.data.cnum;
	                	var cmoney = response.data.cmoney;
	                	var ccnum = [];
	                	var ccnomey = [];
	                	for(var i=0;i<cnum.length;i++){
	                		ccnum.push(Number(cnum[i]));
	                	}
	                	for(var i=0;i<cmoney.length;i++){
	                		ccnomey.push(Number(cmoney[i]));
	                	}
	        			var chart;
	        			$(document).ready(function() {
	        				chart = new Highcharts.Chart({
	        					chart: {
	        						renderTo: 'container',
	        						defaultSeriesType: 'column',
	        						margin: [50, 50, 100, 80]
	        					},
	        					title: {
	        						text: '机构业务数据统计'
	        					},
	        					xAxis: {
	        						categories:response.data.cname,
	        						labels: {
	        							rotation: -45,
	        							align: 'right',
	        							style: {
	        								 font: 'normal 13px Verdana, sans-serif'
	        							}
	        						}
	        					},
	        					yAxis: {
	        						min: 0,
	        						title: {
	        							text: ''
	        						}
	        					},
	        					legend: {
	        						style: {
	        							left: 'auto',
	        							bottom: 'auto',
	        							right: '70px',
	        							top: '70px'
	        						},
	        						backgroundColor: '#FFFFFF'
	        					},
	        					tooltip: {
	        						formatter: function() {
	        							return ''+
	        								this.x +'-'+this.series.name+ ': '+this.y;
	        						}
	        					},
	        					plotOptions: {
	        						column: {
	        							pointPadding: 0.2,
	        							borderWidth: 0
	        						}
	        					},
	        				        series: [{
	        						name: '交易笔数',
	        						data: ccnum
	        				
	        					}, {
	        						name: '业务量（万元）',
	        						data: ccnomey
	        				
	        					}]
	        				});
	        				
	        			});
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
	
	try{
	$('#content_listing').datagrid({
	    dataSource: new DataGridDataSource({
	        // Column definitions for Datagrid
	        columns: [
	                 {
	  	                property: 'date',
	  	                label: '日期',
	  	                sortable: false
	  	            },
	            {
	                property: 'user_code',
	                label: '推广(团队/机构)码',
	                sortable: false
	            },
	            {
	                property: 'username',
	                label: '名称',
	                sortable: false
	            },
	            {
	                property: 'money',
	                label: '业务量(万元)',
	                sortable: false
	            },{
	                property: 'new_money',
	                label: '新手产品业务量(万元)',
	                sortable: false
	            },{
	                property: 'week_money',
	                label: '周周福业务量(万元)',
	                sortable: false
	            },{
	                property: 'month_money',
	                label: '月月福业务量(万元)',
	                sortable: false
	            },{
	                property: 'senson_money',
	                label: '季季福业务量(万元)',
	                sortable: false
	            },{
	                property: 'num',
	                label: '交易笔数',
	                sortable: false
	            },{
	                property: 'new_oder',
	                label: '新手产品交易笔数',
	                sortable: false
	            },{
	                property: 'week_oder',
	                label: '周周福交易笔数',
	                sortable: false
	            },{
	                property: 'month_oder',
	                label: '月月福交易笔数',
	                sortable: false
	            },{
	                property: 'senson_oder',
	                label: '季季福交易笔数',
	                sortable: false
	            }
	        ]
	    /*,
	        formatter: function (items) {
	            $.each(items, function (index, item) {
	            	item.user_code = '<a href="javascript:alert('+"'我是"+item.user_code+"'"+')" >'+ item.user_code+' <i class="fa fa-search-plus"></i></a>';
	            });
	        }*/
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