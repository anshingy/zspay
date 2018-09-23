layui.define(['layer', 'table', 'form','layedit','code'], function(exports){
	var $ = layui.jquery;
	var layer = layui.layer;
	var table = layui.table;
	var form = layui.form;
	var layedit = layui.layedit;
	
	layui.code();
	
	table.render({
		elem: '#table',
		url: '/member/product/ajax',
		page: true,
		cellMinWidth:60,
		cols: [[
			{field: 'hashid', title: '编号', width:80},
			{field: 'name', title: '名称'},
			{field: 'price', title: '售价',width:80},
			{field: 'qty', title: '库存',templet: '#qty',width:80},
			{field: 'sellnum', title: '已售',width:80},
			{field: 'active', title: '状态',templet: '#active',width:120},
			{field: 'opt', title: '操作',templet: '#opt',width:120}
		]]
	});
		 
	//建立编辑器
	var edit_kami_single = layedit.build('kami_single',{
		tool: ['strong','italic','underline','|','del','left','center','right','link','unlink','face']
	});	
	
	form.on('radio(isfaka)', function(data){
		if(data.value=='1'){
			$('#kami_multi_div').show();
			$('#kami_single_div').hide();
		}else{
			$('#kami_single_div').show();
			$('#kami_multi_div').hide();
		}
	}); 
	
	//新增
	form.on('submit(add)', function(data){
		layedit.sync(edit_kami_single);
		data.field.csrf_token = TOKEN;
		
		if(data.field.isfaka>0){
			data.field.kami = $('#kami_multi').val();
		}else{
			data.field.kami = layedit.getContent(edit_kami_single);
		}
		
		if(data.field.kami == null || data.field.kami == ''){
			layer.msg('请输入付费内容',{icon:2,time:5000});
		}else{
			var i = layer.load(2,{shade: [0.5,'#fff']});
			$.ajax({
				url: '/member/product/editajax',
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
							location.href = '/member/product/edit/?hashid='+res.data.hashid;
						},
						cancel: function(){ 
							location.href = '/member/product/edit/?hashid='+res.data.hashid;
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
		}
		return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
	});
	
	//修改
	form.on('submit(edit)', function(data){
		data.field.csrf_token = TOKEN;
			var i = layer.load(2,{shade: [0.5,'#fff']});
			$.ajax({
				url: '/member/product/editajax',
				type: 'POST',
				dataType: 'json',
				data: data.field,
			})
			.done(function(res) {
				if (res.code == '1') {
					layer.open({
						title: '提示',
						content: '修改成功',
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
	
	exports('memberproduct',null)
});