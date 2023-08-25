$('document').ready(function(){
	
	//Load ajax Datatable
	loadDatatable();
	//Load Types list
	loadType();
	//Load Types based on Items
	loadItem();
	
	//Add or Edit functionality
	$('#btn_save').click(function(){
		var action = "save";
		$('.error-block').hide();
		$('.alert').hide();
		let user = $('#name').val();
		if(user == ''){
			$('.user-block').show();
			return false;
		}
		var userdata = $('#name').val();
		var type = $('#type').val();
		var Item = $('#Item').val();
		var txt_userid = $('#txt_userid').val();
		jsonObj = [];
		$(Item).each(function(k,v){
			jsonObj.push({"Item" : v,"type" : type});
		});
        var jsonString = JSON.stringify(jsonObj);
		
		$.ajax({
			url:"loadajaxfile.php",
			method:"POST",
			data:{action:action,user:user,itemdata:jsonString,txt_userid:txt_userid},
			success:function(data)
			{
			 $('.alert').show();
			 $('.alert').html(data);
			 if(txt_userid == 0){
				 $('#name').val('');
				 loadItem();
			 }
			 
			 setTimeout(function(){closepopup();loadDatatable()},2000);
			}
		   });
	});
	
	
	
});
//Load data Function
function loadDatatable(){
	if ($.fn.DataTable.isDataTable("#itemTable")) {
	  $('#itemTable').DataTable().clear().destroy();
	}
			 
	let table = new DataTable('#itemTable', {
		ajax: {
			url: 'loadajaxfile.php',
			type: 'POST',
			"dataSrc": function (d) {
				 return d
			  },
			data: {action:'loaddata'},
		},
		columns: [
			{ data: 'name' },
			{ data: 'items' },
			{ data: 'types' },
			{ data: 'action' },
			
		]
	});
}

//Add/Edit popup
function openpopup(){
	$('.error-block').hide();
	$('.alert').hide();
	$('#exampleModal').modal('show');
	$('#txt_userid').val(0);
	$('#name').val('');
	loadItem();
}
//Close Popup
function closepopup(){
	$('#exampleModal').modal('hide');
}

//Load item
function loadItem(){
	var type_id = $('#type').val();
		var action = "item";
		   $.ajax({
			url:"loadajaxfile.php",
			method:"POST",
			data:{action:action,type_id:type_id},
			success:function(data)
			{
			 $('#Item').html(data);
			 
			}
		   });
	}
	//Load Type
	function loadType(){
		
		var action = "type";
		   $.ajax({
			url:"loadajaxfile.php",
			method:"POST",
			data:{action:action},
			success:function(data)
			{
			 $('#type').html(data);
			}
		   });
	}
	
	//Edit Functionality
	function editData(id){
		
		var action = "edit";
		   $.ajax({
			url:"loadajaxfile.php",
			method:"POST",
			data:{action:action,editid:id},
			success:function(data)
			{
				var returnedData = JSON.parse(data);
				console.log(returnedData.id);
				$('.error-block').hide();
				$('.alert').hide();
				$('#exampleModal').modal('show');
				$('#name').val(returnedData.name);
				$('#txt_userid').val(returnedData.id);
				$('#Item').html(returnedData.item_list);
				$('#type').val(returnedData.type);
				$('#Item').val(returnedData.items);
			}
		   });
	}
	
	//Delete Popup
	function deleteData(id,user){
		$('.alert').hide();
		$('#deleteModal').modal('show');
		$('#del_userid').val(id);
		$('#del_username').val(user);
		
	}
	//Confirm Delete
	function confirmDelete(){
		var action = "delete";
		var delid  = $('#del_userid').val();
		var del_username  = $('#del_username').val();
		   $.ajax({
			url:"loadajaxfile.php",
			method:"POST",
			data:{action:action,delid:delid,del_username:del_username},
			success:function(data)
			{
				$('.alert').show();
				$('.alert').html(data);
				setTimeout(function(){$('#deleteModal').modal('hide');loadDatatable()},2000);
			}
		   });
	}