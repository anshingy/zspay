layui.define(['layer', 'table', 'form'], function(exports){
	var $ = layui.jquery;
	var layer = layui.layer;
	var table = layui.table;
	var form = layui.form;
	
	form.on('select(typeid)', function(data){
		if (data.value == 0) return;
		$.ajax({
			url: '/member/product/getlistbytid',
			type: 'POST',
			dataType: 'json',
			data: {'tid': data.value,'csrf_token':TOKEN},
			beforeSend: function () {
			},
			success: function (res) {
				if (res.code == '1') {
					var html = "";
					var list = res.data.products;
					for (var i = 0, j = list.length; i < j; i++) {
						html += '<option value='+list[i].id+'>'+list[i].name+'</option>';
					}
					$('#productlist').html("<option value=\"0\">请选择</option>" + html);
					form.render('select');
				} else {
					form.render('select');
					layer.msg(res.msg,{icon:2,time:5000});
				}
			}

		});
	});
	

	table.render({
		elem: '#table',
		url: '/member/productscard/ajax',
		page: true,
		cellMinWidth:60,
		cols: [[
			{type: 'checkbox', fixed: 'left'},
			{field: 'id', title: 'ID', width:80},
			{field: 'name', title: '商品名'},
			{field: 'card', title: '卡密'},
			{field: 'addtime', title: '添加时间', width:200, templet: '#addtime',align:'center'},
			{field: 'oid', title: '状态', width:100, templet: '#status',align:'center'},
			{field: 'opt', title: '操作', width:100, toolbar: '#opt',align:'center'},
		]]
	});
  
	//添加
	form.on('submit(add)', function(data){
		data.field.csrf_token = TOKEN;
		var i = layer.load(2,{shade: [0.5,'#fff']});
		$.ajax({
			url: '/member/productscard/addajax',
			type: 'POST',
			dataType: 'json',
			data: data.field,
		})
		.done(function(res) {
			if (res.code == '1') {
				layer.open({
					title: '提示',
					content: '新增成功',
					btn: ['确定'],
					yes: function(index, layero){
					    location.reload();
					},
					cancel: function(){
					    location.reload();
					}
				});
			} else {
				layer.msg(res.msg,{icon:2,time:5000});
			}
		})
		.fail(function() {
			layer.msg('服务器连接失败，请联系管理员',{icon:2,time:5000});
		})
		.always(function() {
			layer.close(i);
		});

		return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
	});

	//批量添加
	form.on('submit(addplus)', function(data){
		data.field.csrf_token = TOKEN;
		var i = layer.load(2,{shade: [0.5,'#fff']});
		$.ajax({
			url: '/member/productscard/addajax',
			type: 'POST',
			dataType: 'json',
			data: data.field,
		})
		.done(function(res) {
			if (res.code == '1') {
				layer.open({
					title: '提示',
					content: '新增成功',
					btn: ['确定'],
					yes: function(index, layero){
					    location.reload();
					},
					cancel: function(){
					    location.reload();
					}
				});
			} else {
				layer.msg(res.msg,{icon:2,time:5000});
			}
		})
		.fail(function() {
			layer.msg('服务器连接失败，请联系管理员',{icon:2,time:5000});
		})
		.always(function() {
			layer.close(i);
		});

		return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
	});
    form.on('submit(search)', function(data){
        table.reload('table', {
            url: '/member/productscard/ajax',
            where: data.field
        });
        return false;
    });
	exports('memberproductscard',null)
});