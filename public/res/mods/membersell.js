layui.define(['layer', 'table'], function(exports){
	var $ = layui.jquery;
	var layer = layui.layer;
	var table = layui.table;

	table.render({
		elem: '#table',
		url: '/member/sell/ajax',
		page: true,
		cellMinWidth:60,
		cols: [[
			{field: 'orderid', title: '订单ID', minWidth:200},
			{field: 'hashid', title: '信息编号', minWidth:120},
			{field: 'productname', title: '信息名称'},
			{field: 'money', title: '金额', width:80},
			{field: 'ip', title: '订单IP', width:120},
			{field: 'paytime', title: '销售时间', width:200, templet: '#addtime',align:'center'}
		]]
	});

	exports('membersell',null)
});