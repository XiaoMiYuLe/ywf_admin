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
	        var url = '/reportadmin/user';
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
                    if(data){
                    	
                    	var user = response.data.user;
	                	var ecoman = response.data.ecoman;
	                	var card = response.data.card;
	                	var day_online = response.data.day_online;
	                	var cuser = [];
	                	var cecoman = [];
	                	var ccard = [];
	                	var cday_online = [];
	                	for(var i=0;i<user.length;i++){
	                		cuser.push(Number(user[i]));
	                	}
	                	for(var i=0;i<ecoman.length;i++){
	                		cecoman.push(Number(ecoman[i]));
	                	}
	                	for(var i=0;i<card.length;i++){
	                		ccard.push(Number(card[i]));
	                	}
	                	for(var i=0;i<day_online.length;i++){
	                		cday_online.push(Number(day_online[i]));
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
                					text: '会员数据统计表'
                				},
                				xAxis: {
                					categories: response.data.cdate,
    					             labels: {
 	        							rotation: -45,
 	        							align: 'right',
 	        							style: {
 	        								 font: 'normal 13px Verdana, sans-serif'
 	        							}
 	        						}
                				},
                				yAxis: {
                					title: {
                						margin:60,
                						rotation:270,
                						text: ''
                					}
                				},
                				tooltip: {
                					formatter: function() {
                						return ''+
                							 this.series.name+'-'+ this.x +': '+this.y;
                					}
                				},
                				credits: {
                					enabled: false
                				},
                				legend: {
	        						style: {
	        							left: 'auto',
	        							bottom: 'auto',
	        							right: '70px',
	        							top: '55px'
	        						},
	        						backgroundColor: '#FFFFFF'
	        					},
                				series: [{
                					name: '注册数量',
                					data: cuser
                				},{
                					name: '在线注册数量',
                					data: cday_online
                				}, {
                					name: '经纪人数量',
                					data: cecoman
                				}, {
                					name: '绑卡数量',
                					data: ccard
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
	                property: 'day_user',
	                label: '注册数量',
	                sortable: false
	            },
	            {
	                property: 'day_online',
	                label: '线上注册数量',
	                sortable: false
	            },
	            {
	                property: 'day_outline',
	                label: '线下注册数量',
	                sortable: false
	            },
	            {
	                property: 'day_ecoman',
	                label: '经纪人数量',
	                sortable: false
	            },
	            {
	                property: 'day_card',
	                label: '绑卡数量',
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